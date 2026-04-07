<?php

namespace App\Models;

use Database, Auth;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use App\Traits\EmailPreferences;
use ESolution\DBEncryption\Traits\EncryptedAttribute;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class Client extends AbstractModelClient implements CanResetPasswordContract, MustVerifyEmail
{
    use Notifiable, CanResetPassword, EmailPreferences;
    protected $rememberTokenName = false;
    protected $table = 'clients';
    protected $dates = ["lastlogin", "datecreated"];
    const STATUS_ACTIVE = "Active";
    const STATUS_INACTIVE = "Inactive";
    const STATUS_CLOSED = "Closed";
    const PAYMENT_DATA_MIGRATED = "--MIGRATED--";
    protected $columnMap = [
        "firstName" => "firstname",
        "lastName" => "lastname",
        "companyName" => "companyname",
        "phoneNumber" => "phonenumber",
        "passwordHash" => "password",
        "twoFactorAuthModule" => "authmodule",
        "twoFactorAuthData" => "authdata",
        "currencyId" => "currency",
        "defaultPaymentGateway" => "defaultgateway",
        "overrideLateFee" => "latefeeoveride",
        "overrideOverdueNotices" => "overideduenotices",
        "disableAutomaticCreditCardProcessing" => "disableautocc",
        "billingContactId" => "billingcid",
        "securityQuestionId" => "securityqid",
        "securityQuestionAnswer" => "securityqans",
        "creditCardType" => "cardtype",
        "creditCardLastFourDigits" => "cardlastfour",
        "creditCardExpiryDate" => "expdate",
        "storedBankNameCrypt" => "bankname",
        "storedBankTypeCrypt" => "banktype",
        "storedBankCodeCrypt" => "bankcode",
        "storedBankAccountCrypt" => "bankacct",
        "paymentGatewayToken" => "gatewayid",
        "lastLoginDate" => "lastlogin",
        "lastLoginIp" => "ip",
        "lastLoginHostname" => "host",
        "passwordResetKey" => "pwresetkey",
        "passwordResetKeyRequestDate" => "pwresetexpiry",
        "passwordResetKeyExpiryDate" => "pwresetexpiry",
        "taxExempt" => "taxexempt",
        "separateInvoices" => "separateinvoices",
        "emailOptOut" => "emailoptout",
        "marketingEmailsOptIn" => "marketing_emails_opt_in",
        "overrideAutoClose" => "overrideautoclose",
        "emailVerified" => "email_verified",
        "email_verified_at" => "email_verified",
        "allowSso" => "allow_sso",
        "groupId" => "groupid",
        "taxId" => "tax_id",
        "emailPreferences" => "email_preferences",
    ];

    protected $guarded = [];

    // protected $encryptable = [
    //     'firstname',
    //     'lastname',
    //     'email',
    //     'address1',
    //     'address2',
    //     'city',
    //     'state',
    //     'postcode',
    //     'phonenumber',
    // ];

    // protected $casts = [
    //     "firstname" => \App\Client::class,
    //     "lastname" => \App\Client::class,
    // ];

    protected $hidden = [
        "password",
        "authdata",
        "securityqans",
        "cardnum",
        "startdate",
        "expdate",
        "issuenumber",
        "bankname",
        "banktype",
        "bankcode",
        "bankacct",
        "pwresetkey",
        "pwresetexpiry"
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = Database::prefix() . $this->table;
        parent::__construct($attributes);
    }

    // public function attributesToArray()
    // {
    //     $attributes = parent::attributesToArray();
    //     foreach ($this->columns as $convention => $actual) {
    //         if (array_key_exists($actual, $attributes)) {
    //             $attributes[$convention] = $attributes[$actual];
    //             unset($attributes[$actual]);
    //         }
    //     }
    //     return $attributes;
    // }

    // public function getAttribute($key)
    // {
    //     if (array_key_exists($key, $this->columns)) {
    //         $key = $this->columns[$key];
    //     }
    //     return parent::getAttributeValue($key);
    // }

    // public function setAttribute($key, $value)
    // {
    //     if (array_key_exists($key, $this->columns)) {
    //         $key = $this->columns[$key];
    //     }
    //     return parent::setAttribute($key, $value);
    // }

    public function getTableName()
    {
        return $this->table;
    }

    public function services()
    {
        return $this->hasMany(Hosting::class, "userid");
    }

    public function getFullNameAttribute()
	{
		return (string) $this->firstname . " " . $this->lastname;
	}

    public function sendPasswordResetNotification($token)
    {
        $data = [
            "token" => $token,
            "userid" => $this->id,
            "name" => $this->fullName,
            "companyName" => $this->companyName ? $this->companyName : $this->fullName,
        ];

        $this->notify(new ResetPasswordNotification($data));
    }

    public function paymethods()
	{
		return $this->hasMany(Paymethod::class, 'id', 'userid');
	}

    public function needsCardDetailsMigrated()
    {
        if ($this->creditCardType) {
            return $this->creditCardType !== self::PAYMENT_DATA_MIGRATED;
        }
        return (bool) trim($this->creditCardLastFourDigits) || (bool) trim($this->cardnum);
    }

    public function isOptedInToMarketingEmails()
    {
        if (\App\Helpers\EmailSubscription::isUsingOptInField()) {
            return (bool) $this->marketingEmailsOptIn;
        }
        return !(bool) $this->emailOptOut;
    }

    public function scopeLoggedIn($query)
    {
        $auth = Auth::user();
        $uid = $auth ? $auth->id : 0;
        return $query->where("id", (int) $uid);
    }

    public function getPaymentGatewayTokenAttribute()
    {
        return $this->attributes['gatewayid'];
    }

    public function billingContact()
    {
        return $this->hasOne(Contact::class, "id", "billingcid");
    }

    public function marketingEmailOptIn($userIp = "", $performCurrentSettingCheck = true)
    {
        if ($performCurrentSettingCheck && $this->isOptedInToMarketingEmails()) {
            throw new \App\Exceptions\Marketing\AlreadyOptedIn();
        }

        $this->emailOptOut = false;
        $this->marketingEmailsOptIn = true;
        $this->save();

        \App\Models\MarketingConsent::logOptIn($this->id, $userIp);
        $this->logActivity("Opted In to Marketing Emails");

        return $this;
    }

    public function marketingEmailOptOut($userIp = "", $performCurrentSettingCheck = true)
    {
        if ($performCurrentSettingCheck && !$this->isOptedInToMarketingEmails()) {
            throw new \App\Exceptions\Marketing\AlreadyOptedOut();
        }

        $this->emailOptOut = true;
        $this->marketingEmailsOptIn = false;
        $this->save();

        \App\Models\MarketingConsent::logOptOut($this->id, $userIp);
        $this->logActivity("Opted Out from Marketing Emails");

        return $this;
    }

    public function contacts()
    {
        return $this->hasMany(\App\Models\Contact::class, "userid", "id");
    }

    public function customFieldValues()
    {
        return $this->hasMany(\App\Models\Customfieldsvalue::class, 'relid', 'id');
    }

    public function logActivity($message)
    {
        \LogActivity::Save("$message - User ID: {$this->id}", $this->id);

        return $this;
    }

    public function transactions()
    {
        return $this->hasMany(Account::class, "userid");
    }

    public function domains()
    {
        return $this->hasMany(Domain::class, "userid");
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class, "userid");
    }

    public function isEmailAddressVerified()
    {
        return (bool) $this->emailVerified;
    }

    public function hasVerifiedEmail()
    {
        return (bool) $this->emailVerified;
    }

    public function orders()
    {
        return $this->hasMany(Order::class, "userid");
    }
}

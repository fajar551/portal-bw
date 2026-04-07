<?php

namespace App\User;

use DB;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;

class Client extends AbstractUser implements Contracts\ContactInterface, UserInterface
{
    protected $table = "tblclients";
    protected $columnMap = array("passwordHash" => "password", "twoFactorAuthModule" => "authmodule", "twoFactorAuthData" => "authdata", "currencyId" => "currency", "defaultPaymentGateway" => "defaultgateway", "overrideLateFee" => "latefeeoveride", "overrideOverdueNotices" => "overideduenotices", "disableAutomaticCreditCardProcessing" => "disableautocc", "billingContactId" => "billingcid", "securityQuestionId" => "securityqid", "securityQuestionAnswer" => "securityqans", "creditCardType" => "cardtype", "creditCardLastFourDigits" => "cardlastfour", "creditCardExpiryDate" => "expdate", "storedBankNameCrypt" => "bankname", "storedBankTypeCrypt" => "banktype", "storedBankCodeCrypt" => "bankcode", "storedBankAccountCrypt" => "bankacct", "paymentGatewayToken" => "gatewayid", "lastLoginDate" => "lastlogin", "lastLoginIp" => "ip", "lastLoginHostname" => "host", "passwordResetKey" => "pwresetkey", "passwordResetKeyRequestDate" => "pwresetexpiry", "passwordResetKeyExpiryDate" => "pwresetexpiry");
    public $timestamps = true;
    protected $dates = array("lastLoginDate", "passwordResetKeyRequestDate", "passwordResetKeyExpiryDate");
    protected $booleans = array("taxExempt", "overrideLateFee", "overrideOverdueNotices", "separateInvoices", "disableAutomaticCreditCardProcessing", "emailOptOut", "marketingEmailsOptIn", "overrideAutoClose", "emailVerified");
    public $unique = array("email");
    protected $appends = array("fullName", "countryName", "groupName");
    // protected $fillable = array("lastlogin", "ip", "host", "pwresetkey", "pwresetexpiry");
    protected $guarded = [];
    protected $hidden = array("password", "authdata", "securityqans", "cardnum", "startdate", "expdate", "issuenumber", "bankname", "banktype", "bankcode", "bankacct", "pwresetkey", "pwresetexpiry");
    const STATUS_ACTIVE = "Active";
    const STATUS_INACTIVE = "Inactive";
    const STATUS_CLOSED = "Closed";
    const PAYMENT_DATA_MIGRATED = "--MIGRATED--";
    public function domains()
    {
        return $this->hasMany(\App\Models\Domain::class, "userid");
    }
    public function services()
    {
        return $this->hasMany(\App\Models\Hosting::class, "userid");
    }
    public function addons()
    {
        return $this->hasMany(\App\Models\Hostingaddon::class, "userid");
    }
    public function contacts()
    {
        return $this->hasMany(\App\Models\Contact::class, "userid");
    }
    public function billingContact()
    {
        return $this->hasOne(\App\Models\Contact::class, "id", "billingcid");
    }
    public function quotes()
    {
        return $this->hasMany(\App\Models\Quote::class, "userid");
    }
    public function affiliate()
    {
        return $this->hasOne(\App\Models\Affiliate::class, "clientid");
    }
    public function securityQuestion()
    {
        return $this->belongsTo(\App\Models\AdminSecurityQuestion::class, "securityqid");
    }
    public function invoices()
    {
        return $this->hasMany(\App\Models\Invoice::class, "userid");
    }
    public function transactions()
    {
        return $this->hasMany(\App\Models\Account::class, "userid");
    }
    public function remoteAccountLinks()
    {
        $relation = $this->hasMany(\App\Models\AuthnAccountLink::class, "client_id");
        $relation->getQuery()->whereNull("contact_id");
        return $relation;
    }
    public function orders()
    {
        return $this->hasMany(\App\Models\Order::class, "userid");
    }
    public function marketingConsent()
    {
        return $this->hasMany(\App\Models\MarketingConsent::class, "userid");
    }
    public function scopeLoggedIn($query)
    {
        return $query->where("id", \Auth::guard('web')->check() ? \Auth::guard('web')->user()->id : 0);
    }
    public function currencyrel()
    {
        return $this->hasOne(\App\Models\Currency::class, "id", "currency");
    }
    public static function getStatuses()
    {
        return array(self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_CLOSED);
    }
    public function hasDomain($domainName)
    {
        $domainCount = $this->domains()->where("domain", "=", $domainName)->count();
        if (0 < $domainCount) {
            return true;
        }
        $serviceDomainCount = $this->services()->where("domain", "=", $domainName)->count();
        return 0 < $serviceDomainCount;
    }
    protected function generateCreditCardEncryptionKey()
    {
        // $config = \Config::self();
        $config = \Config::get('portal')['hash'];
        return md5($config["cc_encryption_hash"] . $this->id);
    }
    // public function getAlerts(Client\AlertFactory $factory = NULL)
    // {
    //     static $alerts = NULL;
    //     if (is_null($alerts)) {
    //         if (is_null($factory)) {
    //             $factory = new Client\AlertFactory($this);
    //         }
    //         $alerts = $factory->build();
    //     }
    //     return $alerts;
    // }
    public function isCreditCardExpiring($withinMonths = 2)
    {
        $cardDetails = \App\Helpets\Cc::getClientDefaultCardDetails($this->id);
        if (empty($cardDetails["expdate"])) {
            return false;
        }
        unset($cardDetails["fullcardnum"]);
        $expiryDate = str_replace("/", "", $cardDetails["expdate"]);
        if (!is_numeric($expiryDate) || strlen($expiryDate) != 4) {
            return false;
        }
        $isExpiring = \App\Helpers\Carbon::createFromFormat("dmy", "01" . $expiryDate)->diffInMonths(\App\Helpers\Carbon::now()->startOfMonth()) <= $withinMonths;
        if ($isExpiring) {
            return $cardDetails;
        }
        return false;
    }
    public function getFullNameAttribute()
    {
        return (string) $this->firstName . " " . $this->lastName;
    }
    public function getCountryNameAttribute()
    {
        static $countries = NULL;
        if (is_null($countries)) {
            $countries = new \App\Helpers\Country();
        }
        return $countries->getName($this->country);
    }
    public function getSecurityQuestionAnswerAttribute($answer)
    {
        return (new \App\Helpers\Pwd())->decrypt($answer);
    }
    public function setSecurityQuestionAnswerAttribute($answer)
    {
        $this->attributes["securityqans"] = (new \App\Helpers\Pwd())->encrypt($answer);
    }
    public function generateCreditCardEncryptedField($value)
    {
        return $this->encryptValue($value, $this->generateCreditCardEncryptionKey());
    }
    public function getUsernameAttribute()
    {
        return $this->email;
    }
    public function hasSingleSignOnPermission()
    {
        return (bool) $this->allowSso;
    }
    public function isAllowedToAuthenticate()
    {
        return $this->status != "Closed";
    }
    public function isEmailAddressVerified()
    {
        return (bool) $this->emailVerified;
    }
    public function getEmailVerificationId()
    {
        // $transientData = \WHMCS\TransientData::getInstance();
        // $transientDataName = $this->id . ":emailVerificationClientKey";
        // $verificationId = self::generateEmailVerificationKey();
        // $verificationExpiry = 86400;
        // $transientData->store($transientDataName, $verificationId, $verificationExpiry);
        // return $verificationId;
        return "";
    }
    public static function generateEmailVerificationKey()
    {
        return sha1(base64_encode(Str::random(64)));
    }
    public function sendEmailAddressVerification()
    {
        // TODO: sendEmailAddressVerification
        // $systemUrl = config('app.url');
        // $templateName = "Client Email Address Verification";
        // $verificationId = $this->getEmailVerificationId();
        // $verificationLinkPath = (string) $systemUrl . "clientarea.php?verificationId=" . $verificationId;
        // $emailVerificationHyperLink = "<a href=\"" . $verificationLinkPath . "\" id=\"hrefVerificationLink\">" . $verificationLinkPath . "</a>";
        // \App\Helpers\Functions::sendMessage($templateName, $this->id, array("client_email_verification_id" => $verificationId, "client_email_verification_link" => $emailVerificationHyperLink));
        // return $this;
        $client = \App\Models\Client::find($this->id);
        $client->sendEmailVerificationNotification();
    }
    public function updateLastLogin(\App\Helpers\Carbon $time = NULL, $ip = NULL, $host = NULL)
    {
        if (!$time) {
            $time = \App\Helpers\Carbon::now();
        }
        if (!$ip) {
            $ip = \Request::ip();
        }
        if (!$host) {
            $host = request()->getHttpHost();
        }
        $this->update(array("lastlogin" => (string) $time->format("YmdHis"), "ip" => $ip, "host" => $host, "pwresetkey" => "", "pwresetexpiry" => 0));
    }
    public function customFieldValues()
    {
        return $this->hasMany(\App\Models\Customfieldsvalue::class, "relid");
    }
    protected function getCustomFieldType()
    {
        return "client";
    }
    protected function getCustomFieldRelId()
    {
        return 0;
    }
    public function hasPermission($permission)
    {
        throw new \RuntimeException("App\\User\\Client::hasPermission" . " not implemented");
    }
    public function tickets()
    {
        return $this->hasMany(\App\Models\Ticket::class, "userid");
    }
    public function isOptedInToMarketingEmails()
    {
        // TODO: if (\WHMCS\Marketing\EmailSubscription::isUsingOptInField()) {
        //     return (bool) $this->marketingEmailsOptIn;
        // }
        return !(bool) $this->emailOptOut;
    }
    public function marketingEmailOptIn($userIp = "", $performCurrentSettingCheck = true)
    {
        if ($performCurrentSettingCheck && $this->isOptedInToMarketingEmails()) {
            throw new \App\Exceptions\Marketing\AlreadyOptedIn();
        }
        $this->emailOptOut = false;
        $this->marketingEmailsOptIn = true;
        $this->save();
        // \WHMCS\Marketing\Consent::logOptIn($this->id, $userIp);
        $this->logActivity("Opted In to Marketing Emails");
        return $this;
    }
    public function marketingEmailOptOut($userIp = "", $performCurrentSettingCheck = true)
    {
        if ($performCurrentSettingCheck && !$this->isOptedInToMarketingEmails()) {
            throw new \WHMCS\Exception\Marketing\AlreadyOptedOut();
        }
        $this->emailOptOut = true;
        $this->marketingEmailsOptIn = false;
        $this->save();
        // TODO: \WHMCS\Marketing\Consent::logOptOut($this->id, $userIp);
        $this->logActivity("Opted Out from Marketing Emails");
        return $this;
    }
    public function logActivity($message)
    {
        \App\Helpers\LogActivity::Save($message . " - User ID: " . $this->id, $this->id);
        return $this;
    }
    public function deleteEntireClient()
    {
        $userid = $this->id;
        \App\Helpers\Hooks::run_hook("PreDeleteClient", array("userid" => $userid));
        \App\Models\Contact::where('userid', $userid)->delete();
        $tblhostingIds = DB::table("tblhosting")->where("userid", $userid)->pluck("id");
        if (!empty($tblhostingIds)) {
            DB::table("tblhostingconfigoptions")->whereIn("relid", $tblhostingIds)->delete();
        }
        $result = \App\Models\Customfield::select('id')->where('type', 'client')->get();
        foreach ($result->toArray() as $data) {
            $customfieldid = $data["id"];
            \App\Models\Customfieldsvalue::where('fieldid', $customfieldid)->where('relid', $userid)->delete();
        }
        $result = \App\Models\Customfield::selectRaw('id,relid')->where('type', 'product')->get();
        foreach ($result->toArray() as $data) {
            $customfieldid = $data["id"];
            $customfieldpid = $data["relid"];
            $result2 = \App\Models\Hosting::select('id')->where('userid', $userid)->where('packageid', $customfieldpid)->get();
            foreach ($result2->toArray() as $data2) {
                $hostingid = $data2["id"];
                \App\Models\Customfieldsvalue::where('fieldid', $customfieldid)->where('relid', $hostingid)->delete();
            }
        }
        $addonCustomFields = DB::table("tblcustomfields")->where("type", "addon")->get(array("id", "relid"));
        foreach ($addonCustomFields as $addonCustomField) {
            $customFieldId = $addonCustomField->id;
            $customFieldAddonId = $addonCustomField->relid;
            $hostingAddons = DB::table("tblhostingaddons")->where("userid", $userid)->where("addonid", $customFieldAddonId)->pluck("id");
            foreach ($hostingAddons as $hostingAddon) {
                $addonId = $hostingAddon->id;
                DB::table("tblcustomfieldsvalues")->where("fieldid", $customFieldId)->where("relid", $addonId)->delete();
            }
        }
        $result = \App\Models\Hosting::select('id')->where('userid', $userid)->get();
        foreach ($result->toArray() as $data) {
            $domainlistid = $data["id"];
            \App\Models\Hostingaddon::where('hostingid', $domainlistid)->delete();
        }
        \App\Models\Order::where('userid', $userid)->delete();
        \App\Models\Hosting::where('userid', $userid)->delete();
        \App\Models\Domain::where('userid', $userid)->delete();
        \App\Models\Email::where('userid', $userid)->delete();
        \App\Models\Invoice::where('userid', $userid)->delete();
        \App\Models\Invoiceitem::where('userid', $userid)->delete();
        $tickets = DB::table("tbltickets")->where("userid", $userid)->pluck("id");
        foreach ($tickets as $ticketId) {
            try {
                \App\Helpers\Ticket::deleteTicket($ticketId);
            } catch (\App\Exceptions\Fatal $e) {
                $this->logActivity($e->getMessage());
                DB::table("tblticketreplies")->where("tid", $ticketId)->delete();
                DB::table("tbltickettags")->where("ticketid", $ticketId)->delete();
                DB::table("tblticketnotes")->where("ticketid", $ticketId)->delete();
                DB::table("tblticketlog")->where("tid", $ticketId)->delete();
                DB::table("tbltickets")->delete($ticketId);
            } catch (\Exception $e) {
            }
        }
        \App\Models\Affiliate::where('clientid', $userid)->delete();
        \App\Models\Note::where('userid', $userid)->delete();
        \App\Models\Credit::where('clientid', $userid)->delete();
        \App\Models\ActivityLog::where('userid', $userid)->delete();
        \App\Models\Sslorder::where('userid', $userid)->delete();
        \App\Models\AuthnAccountLink::where('client_id', $userid)->delete();
        foreach ($this->payMethods as $payMethod) {
            $payMethod->forceDelete();
        }
        \App\Helpers\LogActivity::Save("Client Deleted - ID: " . $userid);
        return $this->delete();
    }
    public static function getGroups()
    {
        static $groups = NULL;
        if (is_null($groups)) {
            $groups = DB::table("tblclientgroups")->orderBy("groupname")->pluck("groupname", "id");
        }
        return $groups;
    }
    public function needsCardDetailsMigrated()
    {
        if ($this->creditCardType) {
            return $this->creditCardType !== self::PAYMENT_DATA_MIGRATED;
        }
        return (bool) trim($this->creditCardLastFourDigits) || (bool) trim($this->cardnum);
    }
    public function needsBankDetailsMigrated()
    {
        $migrationMarker = $this->banktype;
        return $migrationMarker && $migrationMarker !== self::PAYMENT_DATA_MIGRATED;
    }
    public function needsNonCardPaymentTokenMigrated()
    {
        $expiryDate = null;
        if ($this->creditCardExpiryDate) {
            $expiryDate = $this->decryptValue($this->creditCardExpiryDate, $this->generateCreditCardEncryptionKey());
        }
        return !$expiryDate && $this->paymentGatewayToken;
    }
    public function needsAnyPaymentDetailsMigrated()
    {
        return $this->needsCardDetailsMigrated() || $this->needsBankDetailsMigrated() || $this->needsNonCardPaymentTokenMigrated();
    }
    public function migratePaymentDetailsIfRequired($forceInCron = false)
    {
        if (defined("IN_CRON") && !$forceInCron) {
            return NULL;
        }
        try {
            if ($this->needsAnyPaymentDetailsMigrated()) {
                $migration = new \App\Payment\PayMethod\MigrationProcessor();
                $migration->migrateForClient($this);
            }
        } catch (\Exception $e) {
            $this->logActivity("Paymethod migration failed. " . $e->getMessage());
        }
    }
    public function markCardDetailsAsMigrated()
    {
        $this->creditCardType = self::PAYMENT_DATA_MIGRATED;
        $this->save();
        return $this;
    }
    public function markBankDetailsAsMigrated()
    {
        $this->banktype = self::PAYMENT_DATA_MIGRATED;
        $this->save();
        return $this;
    }
    public function markPaymentTokenMigrated()
    {
        $this->paymentGatewayToken = "";
        $this->save();
        return $this;
    }
    public function payMethods()
    {
        return $this->hasMany(\App\Payment\PayMethod\Model::class, "userid");
    }
    public function defaultBillingContact()
    {
        if ($this->billingContactId) {
            return $this->belongsTo(\App\Models\Contact::class, "billingcid");
        }
        return $this->hasOne(static::class, "id");
    }
    public function getGroupNameAttribute()
    {
        $groupName = "";
        if ($this->groupId) {
            $groups = self::getGroups();
            if (array_key_exists($this->groupId, $groups)) {
                $groupName = $groups[$this->groupId];
            }
        }
        return $groupName;
    }
    public function domainSslStatuses()
    {
        return $this->hasMany(\App\Models\Sslstatus::class, "user_id");
    }
    public function generateUniquePlaceholderEmail()
    {
        return "autogen_" . Str::random(6, 0, 2, 0) . "@example.com";
    }
    public function deleteAllCreditCards()
    {
        $this->creditCardType = "";
        $this->creditCardLastFourDigits = "";
        $this->cardnum = "";
        $this->creditCardExpiryDate = "";
        $this->startdate = "";
        $this->issuenumber = "";
        $this->paymentGatewayToken = "";
        $this->save();
        foreach ($this->payMethods as $payMethod) {
            if ($payMethod->isCreditCard()) {
                $payMethod->delete();
            }
        }
    }
    public static function getUsedCardTypes()
    {
        $cardTypes = \App\Payment\PayMethod\Adapter\CreditCard::where("card_type", "!=", "")->distinct("card_type")->pluck("card_type")->toArray();
        $clientCardTypes = self::where("cardtype", "!=", "")->where("cardtype", "!=", self::PAYMENT_DATA_MIGRATED)->distinct("cardtype")->pluck("cardtype")->toArray();
        asort(array_unique(array_merge($cardTypes, $clientCardTypes)));
        return $cardTypes;
    }
}

?>

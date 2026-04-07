<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;
use App\Traits\EmailPreferences;

class Contact extends AbstractModel
{
	use EmailPreferences;

    protected $columns = [
		"clientId" => "userid", 
		"isSubAccount" => "subaccount", 
		"passwordHash" => "password", 
		"receivesDomainEmails" => "domainemails", 
		"receivesGeneralEmails" => "generalemails", 
		"receivesInvoiceEmails" => "invoiceemails", 
		"receivesProductEmails" => "productemails", 
		"receivesSupportEmails" => "supportemails", 
		"receivesAffiliateEmails" => "affiliateemails", 
		"passwordResetKey" => "pwresetkey", 
		"passwordResetKeyExpiryDate" => "pwresetexpiry"
	];

    public static $allPermissions = [
		"profile", 
		"contacts", 
		"products", 
		"manageproducts", 
		"productsso", 
		"domains", 
		"managedomains", 
		"invoices", 
		"quotes", 
		"tickets", 
		"affiliates", 
		"emails", 
		"orders"
	];

	protected $table = 'contacts';
	protected $columnMap = array("clientId" => "userid", "isSubAccount" => "subaccount", "passwordHash" => "password", "receivesDomainEmails" => "domainemails", "receivesGeneralEmails" => "generalemails", "receivesInvoiceEmails" => "invoiceemails", "receivesProductEmails" => "productemails", "receivesSupportEmails" => "supportemails", "receivesAffiliateEmails" => "affiliateemails", "passwordResetKey" => "pwresetkey", "passwordResetKeyExpiryDate" => "pwresetexpiry");
	protected $guarded = [];
    
	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}

	public function attributesToArray()
    {
        $attributes = parent::attributesToArray();
        foreach ($this->columns as $convention => $actual) {
            if (array_key_exists($actual, $attributes)) {
                $attributes[$convention] = $attributes[$actual];
                unset($attributes[$actual]);
            }
        }
        return $attributes;
    }

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

	public function getFullNameAttribute()
	{
		return (string) $this->firstname . " " . $this->lastname;
	}

	public function client()
	{
		return $this->belongsTo(Client::class, "userid");
	}

	public function remoteAccountLinks()
	{
		return $this->hasMany(AuthnAccountLink::class, "contact_id");
	}
	
	public function orders()
	{
		return $this->hasMany(Order::class, "id", "orderid");
	}
}

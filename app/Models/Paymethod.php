<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paymethod extends Model
{
	use SoftDeletes;
	const TYPE_BANK_ACCOUNT = "BankAccount";
	const TYPE_REMOTE_BANK_ACCOUNT = "RemoteBankAccount";
	const TYPE_CREDITCARD_LOCAL = "CreditCard";
	const TYPE_CREDITCARD_REMOTE_MANAGED = "RemoteCreditCard";
	const TYPE_CREDITCARD_REMOTE_UNMANAGED = "PayToken";
	protected $table = 'paymethods';
	protected $dates = array("deleted_at");

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}

	public function payment()
	{
		return $this->morphTo()->withTrashed();
	}
	public function getContactId()
    {
        if ($this->contact_type == "Contact") {
            return $this->contact_id;
        }
        return 0;
    }
	public function getDescription()
    {
        return (string) $this->description;
    }
}

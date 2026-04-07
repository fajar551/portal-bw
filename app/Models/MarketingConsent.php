<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class MarketingConsent extends Model
{
	protected $table = 'marketing_consent';
    protected $booleans = ["opt_in", "admin"];

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}

	public static function logOptIn($userId, $userIp = "") {
        if (empty($userIp)) {
            $userIp = request()->ip();
        }

        $isAdmin = 0 < auth()->guard('admin')->user()->id; 		// session("adminid");

		$consent = new self();
        $consent->userid = $userId;
        $consent->opt_in = true;
        $consent->admin = $isAdmin;
        $consent->ip_address = $userIp;

        return $consent->save();
    }

    public static function logOptOut($userId, $userIp = "") {
        if (empty($userIp)) {
            $userIp = request()->ip();
        }

        $isAdmin = 0 < auth()->guard('admin')->user()->id;		// session("adminid");
        $consent = new self();
        $consent->userid = $userId;
        $consent->opt_in = false;
        $consent->admin = $isAdmin;
        $consent->ip_address = $userIp;

		return $consent->save();
    }
}

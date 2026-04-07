<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class EmailSubscription
{
	const ACTION_OPTIN = "optin";
    const ACTION_OPTOUT = "optout";
	public static function isUsingOptInField()
    {
		return \App\Helpers\Cfg::get('MarketingEmailConvert');
    }
	public function generateOptInUrl($userId, $email)
    {
        return $this->generateOptInOutUrl(self::ACTION_OPTIN, $userId, $email);
    }
    public function generateOptOutUrl($userId, $email)
    {
        return $this->generateOptInOutUrl(self::ACTION_OPTOUT, $userId, $email);
    }
	protected function generateOptInOutUrl($action, $userId, $email)
    {
        $url = fqdnRoutePath("subscription-manage");
        if (strpos($url, "?") === false) {
            $url .= "?";
        } else {
            $url .= "&";
        }
        return $url . "action=" . $action . "&email=" . urlencode($email) . "&key=" . $this->generateKey($userId, $email, $action);
    }
    public function generateKey($userId, $email, $action)
    {
        if ($action == self::ACTION_OPTOUT) {
            $action = "";
        } else {
            $action = self::ACTION_OPTIN;
        }
        return sha1($action . $email . $userId . config('portal.hash.cc_encryption_hash'));
    }
    public function validateKey(\App\Models\Client $client, $action, $key)
    {
        if ($key != $this->generateKey($client->id, $client->email, $action)) {
            throw new InvalidValue("Invalid key");
        }
    }
}

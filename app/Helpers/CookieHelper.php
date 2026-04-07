<?php
namespace App\Helpers;


// Import Model Class here

// Import Package Class here
use Illuminate\Support\Facades\Cookie;

// Import Laravel Class here
use Format, Cfg, LogActivity;


class CookieHelper
{
	protected $request;
	public static $cookiePrefix = "CBMS";

	public function __construct()
	{

	}

    public static function get($name, $treatAsArray = false)
    {
        $cookies = request()->cookie();

        $val = array_key_exists(self::$cookiePrefix .$name, $cookies) ? $cookies[self::$cookiePrefix .$name] : "";
        if ($treatAsArray) {
            $val = json_decode(base64_decode($val), true);
            $val = is_array($val) ? htmlspecialchars_array($val) : array();
        }

        return $val;
    }

    public static function set($name, $value, $expires = 0, $secure = NULL)
    {
        if (is_array($value)) {
            $value = base64_encode(json_encode($value));
        }

        if (!is_numeric($expires)) {
            if (substr($expires, -1) == "m") {
                $expires = time() + substr($expires, 0, -1) * 30 * 24 * 60 * 60;
            } else {
                $expires = 0;
            }
        }

        if (is_null($secure)) {
            // TODO:
            // $whmcs = \DI::make("app");
            // $secure = (bool) $whmcs->isSSLAvailable();
        }

        Cookie::queue(self::$cookiePrefix . $name, $value, $expires, "/", null, $secure, true);
        // return setcookie(self::$cookiePrefix . $name, $value, $expires, "/", null, $secure, true);
    }

    public static function delete($name)
    {
        Cookie::queue(Cookie::forget(self::$cookiePrefix .$name));
        Cookie::forget(self::$cookiePrefix .$name);
    }
}

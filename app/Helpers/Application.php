<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here
use App\Helpers\Cfg;

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use DB;

class Application
{
	/**
	 * isApiRequest
	 */
	public static function isApiRequest()
	{
		if(Request::is('api/*')){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * isAdminAreaRequest
	 */
	public static function isAdminAreaRequest()
	{
        $adminprefix = env("ADMIN_ROUTE_PREFIX");
		if(Request::is("{$adminprefix}/*")){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * isClientAreaRequest
	 */
	public static function isClientAreaRequest()
	{
        $adminprefix = env("ADMIN_ROUTE_PREFIX");
		if(!Request::is("{$adminprefix}/*") && !Request::is('api/*')){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * getLogoUrlForEmailTemplate
	 */
	public static function getLogoUrlForEmailTemplate()
    {
        $logoUrl = trim(Cfg::getValue("LogoURL"));
        if ($logoUrl && substr($logoUrl, 0, 4) != "http") {
            $logoUrl = ltrim($logoUrl, "/");
            $scheme = self::isSSLAvailable() ? "https" : "http";
            $logoUrl = $scheme . "://" . $logoUrl;
        }
        return $logoUrl;
    }

	/**
	 * isSSLAvailable
	 */
	public static function isSSLAvailable()
    {
        return substr(self::getSystemURL(), 0, 5) == "https";
    }

	/**
	 * getSystemURL
	 */
	public static function getSystemURL($withTrailing = true)
    {
        // $url = trim(Cfg::get("SystemURL"));
        $url = trim(config("app.url"));
        if ($url) {
            while (substr($url, -1) == "/") {
                $url = substr($url, 0, -1);
            }
            if ($withTrailing && substr($url, -1) != "/") {
                $url .= "/";
            }
        }
        return $url;
    }

	public static function formatPostedPhoneNumber($field = "phonenumber")
    {
        $phoneNumber = request()->input($field);
        if ($phoneNumber && request()->has("country-calling-code-" . $field)) {
            $phoneNumber = "+" . request()->input("country-calling-code-" . $field) . "." . $phoneNumber;
        }
        return $phoneNumber;
    }

	public static function getRemoteIp()
	{
		return \App\Helpers\CurrentUser::getIP();
	}

    public static function isVisitorIPBanned()
    {
        DB::table("tblbannedips")->where("expires", "<", date("Y-m-d H:i:s"))->delete();
        $visitorIP = self::getRemoteIp();
        $visitorIPParts = explode(".", $visitorIP);
        array_pop($visitorIPParts);
        $remoteIP1 = implode(".", $visitorIPParts) . ".*";
        array_pop($visitorIPParts);
        $remoteIP2 = implode(".", $visitorIPParts) . ".*.*";
        $result = \App\Models\Bannedip::where('ip', $visitorIP)->orWhere('ip', $remoteIP1)->orWhere('ip', $remoteIP2)->orderBy('id', 'DESC');
        $data = $result;
        if ($data->value("id")) {
            return true;
        }
        return false;
    }
}

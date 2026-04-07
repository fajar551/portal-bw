<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CurrentUser
{
	public static function getIP()
    {
        $config = config("portal.config");
        $useLegacyIpLogic = !empty($config["use_legacy_client_ip_logic"]) ? true : false;
        if ($useLegacyIpLogic) {
            $ip = self::getForwardedIpWithoutTrust();
        } else {
            $request = new \App\Helpers\HttpRequest(request()->server());
            $ip = (string) filter_var($request->getClientIp(), FILTER_VALIDATE_IP);
        }
        return $ip;
    }
    public static function getForwardedIpWithoutTrust()
    {
        if (function_exists("apache_request_headers")) {
            $headers = apache_request_headers();
            if (array_key_exists("X-Forwarded-For", $headers)) {
                $userip = explode(",", $headers["X-Forwarded-For"]);
                $ip = trim($userip[0]);
                if (self::isIpv4AndPublic($ip)) {
                    return $ip;
                }
            }
        } else {
            $ip_array = request()->server("HTTP_X_FORWARDED_FOR") ? explode(",", request()->server("HTTP_X_FORWARDED_FOR")) : array();
            if (count($ip_array)) {
                $ip = trim($ip_array[count($ip_array) - 1]);
                if (self::isIpv4AndPublic($ip)) {
                    return $ip;
                }
            }
        }
        if (request()->server("HTTP_X_FORWARDED") && self::isIpv4AndPublic(request()->server("HTTP_X_FORWARDED"))) {
            return request()->server("HTTP_X_FORWARDED");
        }
        if (request()->server("HTTP_FORWARDED_FOR") && self::isIpv4AndPublic(request()->server("HTTP_FORWARDED_FOR"))) {
            return request()->server("HTTP_FORWARDED_FOR");
        }
        if (request()->server("HTTP_FORWARDED") && self::isIpv4AndPublic(request()->server("HTTP_FORWARDED"))) {
            return request()->server("HTTP_FORWARDED");
        }
        if (request()->server("REMOTE_ADDR")) {
            $ip = request()->server("REMOTE_ADDR");
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
        return "";
    }
    public static function getIPHost()
    {
        $usersIP = self::getIP();
        $fullhost = gethostbyaddr($usersIP);
        return $fullhost ? $fullhost : "Unable to resolve hostname";
    }
    public static function isIpv4AndPublic($ip)
    {
        if (!empty($ip) && ip2long($ip) != -1 && ip2long($ip) != false) {
            $private_ips = array(array("0.0.0.0", "2.255.255.255"), array("10.0.0.0", "10.255.255.255"), array("127.0.0.0", "127.255.255.255"), array("169.254.0.0", "169.254.255.255"), array("172.16.0.0", "172.31.255.255"), array("192.0.2.0", "192.0.2.255"), array("192.168.0.0", "192.168.255.255"), array("255.255.255.0", "255.255.255.255"));
            foreach ($private_ips as $r) {
                $min = ip2long($r[0]);
                $max = ip2long($r[1]);
                if ($min <= ip2long($ip) && ip2long($ip) <= $max) {
                    return false;
                }
            }
            return true;
        } else {
            return false;
        }
    }
}

<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class GeoIp
{
	protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	public static function getLookupUrl($ip)
    {
        $ip = preg_replace("/[^a-z0-9:\\.]/i", "", $ip);
        $link = "https://extreme-ip-lookup.com/" . $ip;
        return $link;
    }

	public static function getLookupHtmlAnchor($ip, $classes = NULL, $text = NULL)
    {
        $link = static::getLookupUrl($ip);
        if (is_null($classes)) {
            $classes .= "autoLinked";
        } else {
            if ($classes && is_string($classes)) {
                $classes .= " autoLinked";
            } else {
                $classes = "";
            }
        }
        $text = (string) $text;
        if (!strlen($text)) {
            $text = $ip;
        }
        return sprintf("<a href=\"%s\" class=\"%s\" target=\"_blank\" >%s</a>", $link, $classes, $text);
    }
}

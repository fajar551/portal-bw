<?php
namespace App\Helpers;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Bbcode
{
	
	public function __construct()
	{
		
	}

    public static function transform($text)
    {
        $bbCodeMap = array("b" => "strong", "i" => "em", "u" => "ul", "div" => "div");
        $text = preg_replace("/\\[div=(&quot;|\")(.*?)(&quot;|\")\\]/", "<div class=\"\$2\">", $text);
        foreach ($bbCodeMap as $bbCode => $htmlCode) {
            $text = str_replace("[" . $bbCode . "]", "<" . $htmlCode . ">", $text);
            $text = str_replace("[/" . $bbCode . "]", "</" . $htmlCode . ">", $text);
        }
        return $text;
    }

}

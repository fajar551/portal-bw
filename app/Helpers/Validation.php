<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Validation
{
	public function escapeshellcmd($string)
    {
        if (function_exists("escapeshellcmd") && \App\Helpers\Php::functionEnabled("escapeshellcmd")) {
            return escapeshellcmd($string);
        }
        $shellCharacters = array("#", "&", ";", "`", "|", "*", "?", "~", "<", ">", "^", "(", ")", "[", "]", "{", "}", "\$", chr(10), chr(255));
        if (\App\Helpers\OperatingSystem::isWindows()) {
            $shellCharacters[] = "%";
            $shellCharacters[] = "\\";
            $string = str_replace($shellCharacters, " ", $string);
            $quotePosition = $this->mismatchedQuotePosition($string);
            if ($quotePosition !== false) {
                $string = substr_replace($string, " ", $quotePosition, 1);
            }
            $quotePosition = $this->mismatchedQuotePosition($string, "'");
            if ($quotePosition !== false) {
                $string = substr_replace($string, " ", $quotePosition, 1);
            }
        } else {
            $string = str_replace("\\", "\\\\", $string);
            foreach ($shellCharacters as $shellCharacter) {
                $string = str_replace($shellCharacter, "\\" . $shellCharacter, $string);
            }
            $quotePosition = $this->mismatchedQuotePosition($string);
            if ($quotePosition !== false) {
                $string = substr_replace($string, "\\\"", $quotePosition, 1);
            }
            $quotePosition = $this->mismatchedQuotePosition($string, "'");
            if ($quotePosition !== false) {
                $string = substr_replace($string, "\\'", $quotePosition, 1);
            }
        }
        return $string;
    }
    public function mismatchedQuotePosition($string, $quoteCharacter = "\"")
    {
        return substr_count($string, $quoteCharacter) % 2 == 0 ? false : strrpos($string, $quoteCharacter);
    }
}

<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here
use App\Helpers\Cfg;
use App\Helpers\Hooks;

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Carbon extends \Carbon\Carbon
{
	protected static $days = NULL;
    protected static $shortDays = NULL;
    protected static $months = NULL;
    protected static $shortMonths = NULL;
    protected static $daySuffixes = NULL;
    protected static $timeSuffixes = NULL;
    protected static $supportedLocales = array("af", "ar", "az", "bg", "bn", "ca", "cs", "da", "de", "el", "en", "eo", "es", "et", "eu", "fa", "fi", "fo", "fr", "he", "hr", "hu", "id", "it", "ja", "ko", "lt", "lv", "ms", "nl", "no", "pl", "pt", "pt_BR", "ro", "ru", "sk", "sl", "sq", "sr", "sv", "th", "tr", "uk", "uz", "vi", "zh", "zh-TW");
    const JANUARY = 1;
    const FEBRUARY = 2;
    const MARCH = 3;
    const APRIL = 4;
    const MAY = 5;
    const JUNE = 6;
    const JULY = 7;
    const AUGUST = 8;
    const SEPTEMBER = 9;
    const OCTOBER = 10;
    const NOVEMBER = 11;
    const DECEMBER = 12;
    const JAN = 1;
    const FEB = 2;
    const MAR = 3;
    const APR = 4;
    const JUN = 6;
    const JUL = 7;
    const AUG = 8;
    const SEPT = 9;
    const OCT = 10;
    const NOV = 11;
    const DEC = 12;
    const TH = 0;
    const ND = 1;
    const RD = 2;
    const ST = 3;
    const SUN = 0;
    const MON = 1;
    const TUE = 2;
    const WED = 3;
    const THU = 4;
    const FRI = 5;
    const SAT = 6;
    const AM = 0;
    const PM = 1;
    const am = 2;
    const pm = 3;
	public function translatePassedToFormat($dateTime, $format)
    {
        return self::createFromFormat("Y-m-d H:i:s", $dateTime)->format($format);
    }
    public function translateTimestampToFormat($timestamp, $format)
    {
        return self::createFromTimestamp($timestamp)->format($format);
    }
    public static function setLocale($locale)
    {
        if (!in_array($locale, self::$supportedLocales)) {
            $locale = "en";
        }
        parent::setLocale($locale);
    }
	public function getAdminDateFormat($withTime = false)
    {
        $dateFormat = Cfg::get("DateFormat");
        if (!$dateFormat) {
            $dateFormat = "DD/MM/YYYY";
        }
        $dateFormat = str_replace(array("DD", "MM", "YYYY"), array("d", "m", "Y"), $dateFormat);
        if ($withTime) {
            $dateFormat .= " H:i";
        }
        return $dateFormat;
    }
	public function toAdminDateFormat()
    {
        return parent::format($this->getAdminDateFormat(false));
    }
    public function toAdminDateTimeFormat()
    {
        return parent::format($this->getAdminDateFormat(true));
    }
    public static function createFromAdminDateFormat($dateString)
    {
        return self::createFromFormat((new self())->getAdminDateFormat(), $dateString)->startOfDay();
    }
    public static function createFromAdminDateTimeFormat($dateTimeString)
    {
        return self::createFromFormat((new self())->getAdminDateFormat(true), $dateTimeString);
    }
    public function getClientDateFormat($withTime = false)
    {
        $clientDateFormat = Cfg::get("ClientDateFormat");
        if ($clientDateFormat == "full") {
            $dateFormat = "jS F Y";
        } else {
            if ($clientDateFormat == "shortmonth") {
                $dateFormat = "jS M Y";
            } else {
                if ($clientDateFormat == "fullday") {
                    $dateFormat = "l, F jS, Y";
                } else {
                    $dateFormat = $this->getAdminDateFormat();
                }
            }
        }
        if ($withTime) {
            $dateFormat .= " (H:i)";
        }
        return $dateFormat;
    }
	public function toClientDateFormat()
    {
        $results = Hooks::run_hook("FormatDateForClientAreaOutput", array("date" => $this));
        foreach ($results as $result) {
            if ($result && is_string($result)) {
                return $result;
            }
        }
        return parent::format($this->getClientDateFormat(false));
    }
	public function toClientDateTimeFormat()
    {
        $results = Hooks::run_hook("FormatDateTimeForClientAreaOutput", array("date" => $this));
        foreach ($results as $result) {
            if ($result && is_string($result)) {
                return $result;
            }
        }
        return parent::format($this->getClientDateFormat(true));
    }
	public static function parseDateRangeValue($value, $withTime = false)
    {
        $carbon = new self();
        $format = $carbon->getAdminDateFormat($withTime);
        if (defined("CLIENTAREA")) {
            $format = $carbon->getClientDateFormat($withTime);
        }
        $value = explode(" - ", $value);
        $firstDate = self::createFromFormat($format, $value[0]);
        if (!$withTime) {
            $firstDate->startOfDay();
        }
        if (!empty($value[1])) {
            $secondDate = self::createFromFormat($format, $value[1]);
            if (!$withTime) {
                $secondDate->endOfDay();
            }
        } else {
            $secondDate = $firstDate->copy();
            if (!$withTime) {
                $secondDate->endOfDay();
            }
        }
        $return = array();
        $return[] = $firstDate;
        $return[] = $secondDate;
        $return["from"] = $firstDate;
        $return["to"] = $secondDate;
        return $return;
    }
	public static function fromCreditCard($date)
    {
        $instance = null;
        $dateParts = explode("/", $date);
        if (!empty($date) && count($dateParts) && $dateParts[0] != "00") {
            try {
                $instance = self::createFromCcInput($date);
            } catch (\Exception $e) {
            }
        }
        return $instance;
    }
	public function toCreditCard()
    {
        return parent::format("m/y");
    }
	public static function createFromCcInput($monthYear)
    {
        $monthYear = str_replace(" ", "", $monthYear);
        if (preg_match("/\\/[\\d]{4}\$/", $monthYear)) {
            $format = "m/Y";
        } else {
            if (preg_match("/^[\\d]{4}\$/", $monthYear)) {
                $format = "my";
            } else {
                $format = "m/y";
            }
        }
        return parent::createFromFormat("d" . $format, "01" . $monthYear)->endOfMonth()->endOfDay();
    }
}

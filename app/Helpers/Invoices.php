<?php
namespace App\Helpers;

use DB;

// Import Model Class here

// Import Package Class here
use App\Helpers\Cfg;

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Invoices
{
	public static function isSequentialPaidInvoiceNumberingEnabled()
    {
        return Cfg::get("SequentialInvoiceNumbering") ? true : false;
    }
    public static function getNextSequentialPaidInvoiceNumber()
    {
        $numberToAssign = Cfg::get("SequentialInvoiceNumberFormat");
        $nextNumber = DB::table("tblconfiguration")->where("setting", "SequentialInvoiceNumberValue")->value("value");
        Cfg::setValue("SequentialInvoiceNumberValue", self::padAndIncrement($nextNumber));
        $numberToAssign = str_replace("{YEAR}", date("Y"), $numberToAssign);
        $numberToAssign = str_replace("{MONTH}", date("m"), $numberToAssign);
        $numberToAssign = str_replace("{DAY}", date("d"), $numberToAssign);
        $numberToAssign = str_replace("{NUMBER}", $nextNumber, $numberToAssign);
        return $numberToAssign;
    }
    public static function padAndIncrement($number, $incrementAmount = 1)
    {
        $newNumber = $number + $incrementAmount;
        if (substr($number, 0, 1) == "0") {
            $numberLength = strlen($number);
            $newNumber = str_pad($newNumber, $numberLength, "0", STR_PAD_LEFT);
        }
        return $newNumber;
    }
}

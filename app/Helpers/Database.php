<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class Database
{
	protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
		// $this->prefix = \Config::get('portal.database.table_prefix');
	}

	public static function prefix()
	{
		return config('portal.database.table_prefix');
	}

	public static function db_build_quoted_field($key)
	{
		$field_quote = "`";
		$parts = explode(".", $key, 3);
		foreach ($parts as $k => $name) {
			$clean_name = self::db_make_safe_field($name);
			if ($clean_name !== $name && $field_quote . $clean_name . $field_quote !== $name) {
				exit("Unexpected input field parameter in database query.");
			}
			$parts[$k] = $field_quote . $clean_name . $field_quote;
		}
		return implode(".", $parts);
	}
	public static function db_escape_string($string)
	{
		// $string = mysql_real_escape_string($string);
		$string = ($string);
		return $string;
	}
	public static function db_escape_array($array)
	{
		$array = array_map("db_escape_string", $array);
		return $array;
	}
	public static function db_escape_numarray($array)
	{
		$array = array_map("intval", $array);
		return $array;
	}
	public static function db_build_in_array($array, $allow_empty = false)
	{
		if (!is_array($array)) {
			$array = array();
		}
		foreach ($array as $k => $v) {
			if (!trim($v) && !$allow_empty) {
				unset($array[$k]);
			} else {
				if (is_numeric($v)) {
				} else {
					$array[$k] = "'" . self::db_escape_string($v) . "'";
				}
			}
		}
		return implode(",", $array);
	}
	public static function db_make_safe_field($field)
	{
		return self::db_escape_string(preg_replace("/[^a-z0-9_.,]/i", "", $field));
	}
	public static function db_make_safe_date($date)
	{
		$dateparts = explode("-", $date);
		$date = (int) $dateparts[0] . "-" . str_pad((int) $dateparts[1], 2, "0", STR_PAD_LEFT) . "-" . str_pad((int) $dateparts[2], 2, "0", STR_PAD_LEFT);
		return self::db_escape_string($date);
	}
	public static function db_make_safe_human_date($date)
	{
		$date = (new \App\Helpers\SystemHelper())->toMySQLDate($date);
		return self::db_make_safe_date($date);
	}
	public static function db_is_valid_amount($amount)
	{
		return preg_match("/^-?[0-9\\.]+\$/", $amount) === 1 ? true : false;
	}
	public static function hasColumn($table, $column)
	{
		return Schema::hasColumn($table, $column);
	}
}

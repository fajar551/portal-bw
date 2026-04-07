<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Pwd
{
	public  $applicationConfig='';

	public function __construct()
	{
		$this->applicationConfig = $this->applicationConfig();
	}

	public function encrypt($string)
    {

        $cc_encryption_hash =$this->applicationConfig;
        $key = md5(md5($cc_encryption_hash)) . md5($cc_encryption_hash);
        $hash_key = $this->_hash($key);
        $hash_length = strlen($hash_key);
        $iv = $this->_generate_iv();
        $out = "";
        for ($c = 0; $c < $hash_length; $c++) {
            $out .= chr(ord($iv[$c]) ^ ord($hash_key[$c]));
        }
        $key = $iv;
        for ($c = 0; $c < strlen($string); $c++) {
            if ($c != 0 && $c % $hash_length == 0) {
                $key = $this->_hash($key . substr($string, $c - $hash_length, $hash_length));
            }
            $out .= chr(ord($key[$c % $hash_length]) ^ ord($string[$c]));
        }
        return base64_encode($out);
    }

	public function decrypt($string)
    {

        $cc_encryption_hash = $this->applicationConfig;
        $key = md5(md5($cc_encryption_hash)) . md5($cc_encryption_hash);
        $hash_key = $this->_hash($key);
        $hash_length = strlen($hash_key);
        $string = base64_decode($string);
        $tmp_iv = substr($string, 0, $hash_length);
        $string = substr($string, $hash_length, strlen($string) - $hash_length);
        $iv = "";
        $out = "";
        for ($c = 0; $c < $hash_length; $c++) {
            $ivValue = isset($tmp_iv[$c]) ? $tmp_iv[$c] : "";
            $hashValue = isset($hash_key[$c]) ? $hash_key[$c] : "";
            $iv .= chr(ord($ivValue) ^ ord($hashValue));
        }
        $key = $iv;
        for ($c = 0; $c < strlen($string); $c++) {
            if ($c != 0 && $c % $hash_length == 0) {
                $key = $this->_hash($key . substr($out, $c - $hash_length, $hash_length));
            }
            $out .= chr(ord($key[$c % $hash_length]) ^ ord($string[$c]));
        }
        return $out;
    }

	public function _hash($string)
    {
        if (function_exists("sha1")) {
            $hash = sha1($string);
        } else {
            $hash = md5($string);
        }
        $out = "";
        $c = 0;
        while ($c < strlen($hash)) {
            $out .= chr(hexdec($hash[$c] . $hash[$c + 1]));
            $c += 2;
        }
        return $out;
    }

	public static function applicationConfig(){
		return config('portal.hash.cc_encryption_hash');
	}

	private function _generate_iv()
    {

        srand((double) microtime() * 1000000);
        $iv = md5(strrev(substr($this->applicationConfig, 13)) . substr($this->applicationConfig, 0, 13));
        $iv .= rand(0, getrandmax());
        $iv .= $this->safe_serialize(array("key" => md5(md5($this->applicationConfig)) . md5($this->applicationConfig)));
        return $this->_hash($iv);
    }

	private function _safe_serialize($value)
    {
        if (is_null($value)) {
            return "N;";
        }
        if (is_bool($value)) {
            return "b:" . (int) $value . ";";
        }
        if (is_int($value)) {
            return "i:" . $value . ";";
        }
        if (is_float($value)) {
            return "d:" . str_replace(",", ".", $value) . ";";
        }
        if (is_string($value)) {
            return "s:" . strlen($value) . ":\"" . $value . "\";";
        }
        if (is_array($value)) {
            $out = "";
            foreach ($value as $k => $v) {
                $out .= $this->_safe_serialize($k) . $this->_safe_serialize($v);
            }
            return "a:" . count($value) . ":{" . $out . "}";
        } else {
            return false;
        }
    }
    public function safe_serialize($value)
    {
        if (function_exists("mb_internal_encoding") && (int) ini_get("mbstring.func_overload") & 2) {
            $mbIntEnc = mb_internal_encoding();
            mb_internal_encoding("ASCII");
        }
        try {
            $out = $this->_safe_serialize($value);
        } catch (\Exception $e) {
			Log::debug($e->getMessage());
            return NULL;
        }
        if (isset($mbIntEnc)) {
            mb_internal_encoding($mbIntEnc);
        }
        return $out;
    }


}

<?php
namespace App\Helpers;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CoreDomains
{
	private $id = 0;
    private $data = array();
    private $domainModel = NULL;
    private $moduleresults = array();
    private $domainInformation = NULL;
    private $registrarModule = NULL;

	public function __construct()
	{
		
	}

    /**testing */
	public function splitAndCleanDomainInput($domain)
    {
        $domain = trim($domain);
        if (substr($domain, -1, 1) == "/") {
            $domain = substr($domain, 0, -1);
        }
        if (substr($domain, 0, 8) == "https://") {
            $domain = substr($domain, 8);
        }
        if (substr($domain, 0, 7) == "http://") {
            $domain = substr($domain, 7);
        }
        if (strpos($domain, ".") !== false) {
            $domain = $this->stripOutSubdomains($domain);
            $domainparts = explode(".", $domain, 2);
            $sld = $domainparts[0];
            $tld = isset($domainparts[1]) ? "." . $domainparts[1] : "";
        } else {
            $sld = $domain;
            $tld = "";
        }
        $sld = $this->clean($sld);
        $tld = $this->clean($tld);
        return array("sld" => $sld, "tld" => $tld);
    }
    /**testing */
	protected function stripOutSubdomains($domain)
    {
        $domain = preg_replace("/^www\\./", "", $domain);
        return $domain;
    }
	/**testing */
	public function clean($val)
    {
        $val = trim($val);
        if (!\App\Helpers\Cfg::get('AllowIDNDomains')) {
            $val = strtolower($val);
        } else {
            if (function_exists("mb_strtolower")) {
                $val = mb_strtolower($val);
            }
        }
        return $val;
    }

	/*** test */
    public function checkDomainisValid($parts)
    {
        global $CONFIG;
        $sld = $parts["sld"];
        $tld = $parts["tld"];
        if ($sld[0] == "-" || $sld[strlen($sld) - 1] == "-") {
            return 0;
        }
        $isIdn = $isIdnTld = $skipAllowIDNDomains = false;
        if ($CONFIG["AllowIDNDomains"]) {
            $idnConvert = new \App\Helpers\Domain\Idna();
            $idnConvert->encode($sld);
            if ($idnConvert->get_last_error() && $idnConvert->get_last_error() != "The given string does not contain encodable chars") {
                return 0;
            }
            if ($idnConvert->get_last_error() && $idnConvert->get_last_error() == "The given string does not contain encodable chars") {
                $skipAllowIDNDomains = true;
            } else {
                $isIdn = true;
            }
        }
        if ($isIdn === false) {
            if (preg_replace("/[^.%\$^'#~@&*(),_£?!+=:{}[]()|\\/ \\\\ ]/", "", $sld)) {
                return 0;
            }
            if ((!$CONFIG["AllowIDNDomains"] || $skipAllowIDNDomains === true) && preg_replace("/[^a-z0-9-.]/i", "", $sld . $tld) != $sld . $tld) {
                return 0;
            }
            if (preg_replace("/[^a-z0-9-.]/", "", $tld) != $tld) {
                return 0;
            }
            $validMask = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-";
            if (strspn($sld, $validMask) != strlen($sld)) {
                return 0;
            }
        }
            \App\Helpers\Hooks::run_hook("DomainValidation", array("sld" => $sld, "tld" => $tld));

        if ($sld === false && $sld !== 0 || !$tld) {
            return 0;
        }
        $coreTLDs = array(".com", ".net", ".org", ".info", "biz", ".mobi", ".name", ".asia", ".tel", ".in", ".mn", ".bz", ".cc", ".tv", ".us", ".me", ".co.uk", ".me.uk", ".org.uk", ".net.uk", ".ch", ".li", ".de", ".jp");
        $DomainMinLengthRestrictions = $DomainMaxLengthRestrictions = array();
        //require ROOTDIR . "/configuration.php";
        foreach ($coreTLDs as $cTLD) {
            if (!array_key_exists($cTLD, $DomainMinLengthRestrictions)) {
                $DomainMinLengthRestrictions[$cTLD] = 3;
            }
            if (!array_key_exists($cTLD, $DomainMaxLengthRestrictions)) {
                $DomainMaxLengthRestrictions[$cTLD] = 63;
            }
        }
        if (array_key_exists($tld, $DomainMinLengthRestrictions) && strlen($sld) < $DomainMinLengthRestrictions[$tld]) {
            return 0;
        }
        if (array_key_exists($tld, $DomainMaxLengthRestrictions) && $DomainMaxLengthRestrictions[$tld] < strlen($sld)) {
            return 0;
        }
        return 1;
    }

	

	

	
   

}

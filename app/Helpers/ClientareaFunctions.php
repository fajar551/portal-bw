<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ClientareaFunctions
{
	public static function initialiseClientArea($pageTitle, $displayTitle, $tagline, $pageIcon = NULL, $breadcrumb = NULL, $smartyValues = array())
	{
		global $_LANG;
		global $smarty;
		global $smartyvalues;
		if ($smartyValues) {
			$smartyvalues = array_merge($smartyvalues, $smartyValues);
		}
		if (defined("PERFORMANCE_DEBUG")) {
			define("PERFORMANCE_STARTTIME", microtime());
		}
		if (is_null($pageIcon) && is_null($breadcrumb)) {
			$pageIcon = $displayTitle;
			$displayTitle = $pageTitle;
			$breadcrumb = $tagline;
			$tagline = "";
		}
		$emptyTemplateParameters = array("displayTitle", "tagline", "type", "textcenter", "hide", "additionalClasses", "idname", "errorshtml", "title", "msg", "desc", "errormessage", "livehelpjs");
		foreach ($emptyTemplateParameters as $templateParam) {
			$smartyvalues[$templateParam] = "";
		}
		$carbonObject = new \App\Helpers\Carbon();
		$smartyvalues["showbreadcrumb"] = false;
		$smartyvalues["showingLoginPage"] = false;
		$smartyvalues["incorrect"] = false;
		$smartyvalues["kbarticle"] = array("title" => "");
		$smartyvalues["language"] = "";
		$smartyvalues["LANG"] = $_LANG;
		$smartyvalues["companyname"] = \App\Helpers\Cfg::getValue("CompanyName");
		$smartyvalues["logo"] = \App\Helpers\Cfg::getValue("LogoURL");
		$smartyvalues["charset"] = \App\Helpers\Cfg::getValue("Charset");
		$smartyvalues["pagetitle"] = $pageTitle;
		$smartyvalues["displayTitle"] = $displayTitle;
		$smartyvalues["tagline"] = $tagline;
		$smartyvalues["pageicon"] = $pageIcon;
		$smartyvalues["filename"] = "";
		$smartyvalues["breadcrumb"] = "";
		$smartyvalues["breadcrumbnav"] = "";
		$smartyvalues["todaysdate"] = $carbonObject->format("l, jS F Y");
		$smartyvalues["date_day"] = $carbonObject->format("d");
		$smartyvalues["date_month"] = $carbonObject->format("m");
		$smartyvalues["date_year"] = $carbonObject->format("Y");
		$smartyvalues["token"] = csrf_token();
		$smartyvalues["reCaptchaPublicKey"] = \App\Helpers\Cfg::getValue("ReCAPTCHAPublicKey");
		$smartyvalues["servedOverSsl"] = "";
		$smartyvalues["versionHash"] = "";
		$smartyvalues["systemurl"] = config('app.url');
		$smartyvalues["systemsslurl"] = config('app.url');
		$smartyvalues["systemNonSSLURL"] = config('app.url');
		$smartyvalues["WEB_ROOT"] = "";
		$smartyvalues["BASE_PATH_CSS"] = "";
		$smartyvalues["BASE_PATH_JS"] = "";
		$smartyvalues["BASE_PATH_FONTS"] = "";
		$smartyvalues["BASE_PATH_IMG"] = "";

		$currenciesarray = array();
		$result = \App\Models\Currency::orderBy("code", "ASC")->get();
		foreach ($result->toArray() as $data) {
			$currenciesarray[] = array("id" => $data["id"], "code" => $data["code"], "default" => $data["default"]);
		}
		if (count($currenciesarray) == 1) {
			$currenciesarray = [];
		}
		$smartyvalues["currencies"] = $currenciesarray;
	}

	public static function outputClientArea($templatefile, $nowrapper = false, $hookFunctions = array(), $smartyValues = array())
	{
		global $CONFIG;
		global $smarty;
		global $smartyvalues;
		global $orderform;
		global $usingsupportmodule;
		if (!empty($smartyValues)) {
			// $smartyvalues = $smartyValues;
			$smartyvalues = array_merge($smartyvalues ?? [], $smartyValues);
		}
		if (!$templatefile) {
			throw new \Exception("Invalid Entity Requested");
		}

		// hooks
		$hookParameters = $smartyvalues;
		$hookFunctions = array_merge(array("ClientAreaPage"), $hookFunctions);
		foreach ($hookFunctions as $hookFunction) {
			$hookResponses = \App\Helpers\Hooks::run_hook($hookFunction, $hookParameters);
			foreach ($hookResponses as $hookTemplateVariables) {
				if (is_array($hookTemplateVariables)) {
					foreach ($hookTemplateVariables as $k => $v) {
						$hookParameters[$k] = $v;
						if (isset($smartyvalues[$k])) {
							$smartyvalues[$k] = $v;
						}
						// $smarty->assign($k, $v);
						$smartyvalues[$k] = $v;
					}
				}
			}
		}

        $hookResponses = \App\Helpers\Hooks::run_hook("ClientAreaHeadOutput", $hookParameters);
        $headOutput = "";
        foreach ($hookResponses as $response) {
            if ($response) {
                $headOutput .= $response . "\n";
            }
        }
        $smartyvalues["headoutput"] = $headOutput;

        $hookResponses = \App\Helpers\Hooks::run_hook("ClientAreaHeaderOutput", $hookParameters);
        $headerOutput = "";
        foreach ($hookResponses as $response) {
            if ($response) {
                $headerOutput .= $response . "\n";
            }
        }
        $smartyvalues["headeroutput"] = $headerOutput;
        $hookResponses = \App\Helpers\Hooks::run_hook("ClientAreaFooterOutput", $hookParameters);
        $footerOutput = "";
        foreach ($hookResponses as $response) {
            if ($response) {
                $footerOutput .= $response . "\n";
            }
        }
        if (array_key_exists("credit_card_input", $smartyvalues) && $smartyvalues["credit_card_input"]) {
            $footerOutput .= $smartyvalues["credit_card_input"];
            unset($smartyvalues["credit_card_input"]);
        }
        $smartyvalues["footeroutput"] = $footerOutput;

		// dd($smartyvalues);
		return view($templatefile, $smartyvalues);
	}
}

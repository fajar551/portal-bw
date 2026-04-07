<?php

namespace Modules\Fraud\MaxMind\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MaxMindController extends Controller
{
    public function MetaData($params = [])
    {
        return array("DisplayName" => "MaxMind", "SupportsRechecks" => true, "APIVersion" => "1.2");
    }

    public function getConfigArray()
    {
        return array("Enable" => array("FriendlyName" => "Enable MaxMind", "Type" => "yesno", "Description" => "Tick to enable MaxMind Fraud Checking for Orders"), "userId" => array("FriendlyName" => "MaxMind User ID", "Type" => "text", "Size" => "30", "Description" => "Don't have an account? <a href=\"http://go.whmcs.com/78/maxmind\" class=\"autoLinked\">Click here to sign up &raquo;</a>"), "licenseKey" => array("FriendlyName" => "MaxMind License Key", "Type" => "text", "Size" => "30"), "serviceType" => array("FriendlyName" => "Service Type", "Default" => "Insights", "Type" => "dropdown", "Options" => implode(",", array("Score", "Insights", "Factors")), "Description" => "Determines the level of checks that are performed. Default is <strong>Score</strong>. <a href=\"http://go.whmcs.com/1349/maxmind-compare\" class=\"autoLinked\">Learn more</a>"), "riskScore" => array("FriendlyName" => "MaxMind Fraud Risk Score", "Type" => "text", "Size" => "2", "Default" => 20, "Description" => "Higher than this value and the order will be blocked (0.01 -> 99)"), "ignoreAddressValidation" => array("FriendlyName" => "Do Not Validate Address Information", "Type" => "yesno", "Description" => "Tick to ignore warnings related to address information validation failing."), "rejectFreeEmail" => array("FriendlyName" => "Reject Free Email Service", "Type" => "yesno", "Description" => "Block orders from free email addresses such as Hotmail & Yahoo!<sup>*</sup>"), "rejectCountryMismatch" => array("FriendlyName" => "Reject Country Mismatch", "Type" => "yesno", "Description" => "Block orders where order address is different from IP Location<sup>*</sup>"), "rejectAnonymousNetwork" => array("FriendlyName" => "Reject Anonymous Networks", "Type" => "yesno", "Description" => "Block orders where the user is ordering through an anonymous network<sup>*</sup>"), "rejectHighRiskCountry" => array("FriendlyName" => "Reject High Risk Country", "Type" => "yesno", "Description" => "Block orders from high risk countries<sup>*</sup>"), "customRules" => array("FriendlyName" => "Custom Rules", "Type" => "System", "Description" => "Additional rules can be created within your MaxMind account to apply automated fraud check filtering based on rules and criteria you define.<br>For more information about custom rules, visit the <a href=\"http://go.whmcs.com/1353/maxmind-custom-rules\" class=\"autoLinked\">MaxMind website</a>"), "<div class=\"pull-right\">*</div>" => array("Type" => "System", "Description" => "Only Available for Insights & Factors"));
    }

    public function doFraudCheck(array $params, $checkOnly = false)
    {
        $returnData["data"] = array("title" => 'Data title', "description" => 'Data description');
        return $returnData;
    }

    public function processResultsForDisplay(array $params)
    {
        return "success";
    }
}

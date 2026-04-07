<?php
namespace App\Helpers;

// Import Model Class here
use App\Models\Product;
use App\Models\Productconfiglink;
use App\Models\Productconfigoption;

// Import Package Class here
use DB, Auth;
use App\Helpers\Application;
use App\Helpers\Database;

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ConfigOptions
{
	public static function getCartConfigOptionsOLD($pid, $values, $cycle, $accountid = "", $orderform = "", $showHiddenOverride = false)
	{
        global $CONFIG;
        global $_LANG;
        global $currency;
        $auth = Auth::guard('admin')->user();
        $adminid = $auth ? $auth->id : 0;
        if (!is_array($currency) || !array_key_exists("id", $currency)) {
            $currency = \App\Helpers\Format::getCurrency();
        }
        $configoptions = array();
        $cycle = strtolower(str_replace(array("-", " "), "", $cycle));
        if ($cycle == "onetime") {
            $cycle = "monthly";
        }

        $showhidden = $showHiddenOverride || $adminid && (Application::isAdminAreaRequest() || Application::isClientAreaRequest()) ? true : false;
        $cyclemonths = \App\Helpers\Invoice::getBillingCycleMonths($cycle);

        if ($accountid) {
            $values = $options = array();
            $accountid = (int) $accountid;
            $query = "SELECT tblproductconfigoptionssub.id, tblproductconfigoptionssub.configid\nFROM tblproductconfigoptionssub\nINNER JOIN tblproductconfigoptions ON tblproductconfigoptionssub.configid = tblproductconfigoptions.id\nINNER JOIN tblproductconfiglinks ON tblproductconfigoptions.gid = tblproductconfiglinks.gid\nINNER JOIN tblhosting on tblproductconfiglinks.pid = tblhosting.packageid\nWHERE tblhosting.id = " . $accountid . "\nAND tblproductconfigoptions.optiontype IN (3, 4)\nGROUP BY tblproductconfigoptionssub.configid\nORDER BY tblproductconfigoptionssub.sortorder ASC, id ASC;";
            $configOptionsResult = DB::select(DB::raw($query));
            foreach ($configOptionsResult as $configOptionsData) {
                $options[$configOptionsData->id] = $configOptionsData->configid;
            }
            if (count($options)) {
                foreach ($options as $subID => $configOptionID) {
                    $isOptionSaved = (bool) \App\Models\Hostingconfigoption::where('configid', 1)->where('relid', 166)->value('configid');
                    if (!$isOptionSaved) {
                        $h = new \App\Models\Hostingconfigoption;
                        $h->relid = $accountid;
                        $h->configid = $configOptionID;
                        $h->optionid = $subID;
                        $h->qty = 0;
                        $h->save();
                    }
                }
            }
            $result = \App\Models\Hostingconfigoption::where('relid', $accountid)->get();
            foreach ($result->toArray() as $data) {
                $configid = $data["configid"];
                $result2 = \App\Models\Productconfigoption::find($configid);
                $data2 = $result2->toArray();
                $optiontype = $data2["optiontype"];
                if ($optiontype == 3 || $optiontype == 4) {
                    $configoptionvalue = $data["qty"];
                } else {
                    $configoptionvalue = $data["optionid"];
                }
                $values[$configid] = $configoptionvalue;
            }
        }

        $result2 = \App\Models\Productconfigoption::query();
        $result2->select("tblproductconfigoptions.*");
        $result2->where("pid", $pid);
        if (!$showhidden) {
            $result2->where("hidden", 0);
        }
        $result2->orderBy("tblproductconfigoptions.order", "ASC");
        $result2->orderBy("tblproductconfigoptions.id", "ASC");
        $result2->join("tblproductconfiglinks", "tblproductconfiglinks.gid", "=", "tblproductconfigoptions.gid");
        foreach ($result2->get()->toArray() as $data2) {
            $optionid = $data2["id"];
            $optionname = $data2["optionname"];
            $optiontype = $data2["optiontype"];
            $optionhidden = $data2["hidden"];
            $qtyminimum = $data2["qtyminimum"];
            $qtymaximum = $data2["qtymaximum"];
            if (strpos($optionname, "|")) {
                $optionname = explode("|", $optionname);
                $optionname = trim($optionname[1]);
            }
            $options = array();
            $selname = $selectedoption = "";
            $selsetup = $selrecurring = 0;
            $selectedqty = 0;
            $foundPreselectedValue = false;
            $selvalue = isset($values[$optionid]) ? $values[$optionid] : "";

            if ($optiontype == "3") {
                $result3 = \App\Models\Productconfigoptionssub::where("configid", $optionid)->first();
                $data3 = $result3->toArray();
                $opid = $data3["id"];
                $ophidden = $data3["hidden"];
                $opname = $data3["optionname"];
                if (strpos($opname, "|")) {
                    $opname = explode("|", $opname);
                    $opname = trim($opname[1]);
                }
                $opnameonly = $opname;
                $result4 = \App\Models\Pricing::where("type", "configoptions")->where("currency", $currency["id"])->where("relid", $opid)->first();
                $data = $result4->toArray();
                $setup = isset($data[$cycle]) ? $data[substr($cycle, 0, 1) . "setupfee"] : 0;
                $price = $fullprice = isset($data[$cycle]) ? $data[$cycle] : 0;
                if ($orderform && $CONFIG["ProductMonthlyPricingBreakdown"]) {
                    $price = $price / $cyclemonths;
                }
                if (0 < $price) {
                    $opname .= " " . \App\Helpers\Format::formatCurrency($price);
                }
                $setupvalue = 0 < $setup ? " + " . \App\Helpers\Format::formatCurrency($setup) . " " . $_LANG["ordersetupfee"] : "";
                $options[] = array("id" => $opid, "hidden" => $ophidden, "name" => $opname . $setupvalue, "nameonly" => $opnameonly, "recurring" => $price);
                if (!$selvalue) {
                    $selvalue = 0;
                }
                $selectedqty = $selvalue;
                $selvalue = $opid;
                $selname = $_LANG["no"];
                if ($selectedqty) {
                    $selname = $_LANG["yes"];
                    $selectedoption = $opname;
                    $selsetup = $setup;
                    $selrecurring = $fullprice;
                }
            } else {
                if ($optiontype == "4") {
                    $result3 = \App\Models\Productconfigoptionssub::where("configid", $optionid)->first();
                    $data3 = $result3->toArray();
                    $opid = $data3["id"];
                    $ophidden = $data3["hidden"];
                    $opname = $data3["optionname"];
                    if (strpos($opname, "|")) {
                        $opname = explode("|", $opname);
                        $opname = trim($opname[1]);
                    }
                    $opnameonly = $opname;
                    $result4 = \App\Models\Pricing::where("type", "configoptions")->where("currency", $currency["id"])->where("relid", $opid)->first();
                    $data = $result4->toArray();
                    // $setup = $data[substr($cycle, 0, 1) . "setupfee"];
                    $setup = isset($data[$cycle]) ? $data[substr($cycle, 0, 1) . "setupfee"] : 0;
                    // $price = $fullprice = $data[$cycle];
                    $price = $fullprice = isset($data[$cycle]) ? $data[$cycle] : 0;
                    if ($orderform && $CONFIG["ProductMonthlyPricingBreakdown"]) {
                        $price = $price / $cyclemonths;
                    }
                    if (0 < $price) {
                        $opname .= " " . \App\Helpers\Format::formatCurrency($price);
                    }
                    $setupvalue = 0 < $setup ? " + " . \App\Helpers\Format::formatCurrency($setup) . " " . $_LANG["ordersetupfee"] : "";
                    $options[] = array("id" => $opid, "hidden" => $ophidden, "name" => $opname . $setupvalue, "nameonly" => $opnameonly, "recurring" => $price);
                    if (!is_numeric($selvalue) || $selvalue < 0) {
                        $selvalue = $qtyminimum;
                    }
                    if (0 < $qtyminimum && $selvalue < $qtyminimum) {
                        $selvalue = $qtyminimum;
                    }
                    if (0 < $qtymaximum && $qtymaximum < $selvalue) {
                        $selvalue = $qtymaximum;
                    }
                    $selectedqty = $selvalue;
                    $selvalue = $opid;
                    $selname = $selectedqty;
                    $selectedoption = $opname;
                    $selsetup = $setup * $selectedqty;
                    $selrecurring = $fullprice * $selectedqty;
                } else {
                    $result3 = \App\Models\Productconfigoptionssub::select("tblpricing.*", "tblproductconfigoptionssub.*")
                        ->where("tblproductconfigoptionssub.configid", $optionid)
                        ->where("tblpricing.type", "configoptions")
                        ->where("tblpricing.currency", $currency["id"])
                        ->orderBy("tblproductconfigoptionssub.sortorder", "ASC")
                        ->orderBy("tblproductconfigoptionssub.id", "ASC")
                        ->join("tblpricing", "tblpricing.relid", "=", "tblproductconfigoptionssub.id")
                        ->get();
                    foreach ($result3->toArray() as $data3) {
                        $opid = $data3["id"];
                        $ophidden = $data3["hidden"];
                        // $setup = $data3[substr($cycle, 0, 1) . "setupfee"];
                        $setup = isset($data[$cycle]) ? $data[substr($cycle, 0, 1) . "setupfee"] : 0;
                        // $price = $fullprice = $data3[$cycle];
                        $price = $fullprice = isset($data[$cycle]) ? $data[$cycle] : 0;
                        if ($orderform && $CONFIG["ProductMonthlyPricingBreakdown"]) {
                            $price = $price / $cyclemonths;
                        }
                        $setupvalue = 0 < $setup ? " + " . \App\Helpers\Format::formatCurrency($setup) . " " . $_LANG["ordersetupfee"] : "";
                        $rawName = $required = $opname = $data3["optionname"];
                        if (strpos($opname, "|")) {
                            $opnameArr = explode("|", $opname);
                            $opname = trim($opnameArr[1]);
                            $required = trim($opnameArr[0]);
                            if (defined("APICALL")) {
                                $setupvalue = "";
                            }
                        }
                        $opnameonly = $opname;
                        if (0 < $price && !defined("APICALL")) {
                            $opname .= " " . \App\Helpers\Format::formatCurrency($price);
                        }
                        if ($showhidden || !$ophidden || $opid == $selvalue) {
                            $options[] = array("id" => $opid, "name" => $opname . $setupvalue, "rawName" => $rawName, "required" => $required, "nameonly" => $opnameonly, "nameandprice" => $opname, "setup" => $setup, "fullprice" => $fullprice, "recurring" => $price, "hidden" => $ophidden);
                        }
                        if ($opid == $selvalue || !$selvalue && !$ophidden) {
                            $selname = $opnameonly;
                            $selectedoption = $opname;
                            $selsetup = $setup;
                            $selrecurring = $fullprice;
                            $selvalue = $opid;
                            $foundPreselectedValue = true;
                        }
                    }
                    if (!$foundPreselectedValue && 0 < count($options)) {
                        $selname = $options[0]["nameonly"];
                        $selectedoption = $options[0]["nameandprice"];
                        $selsetup = $options[0]["setup"];
                        $selrecurring = $options[0]["fullprice"];
                        $selvalue = $options[0]["id"];
                    }
                }
            }
            $configoptions[] = array("id" => $optionid, "hidden" => $optionhidden, "optionname" => $optionname, "optiontype" => $optiontype, "selectedvalue" => $selvalue, "selectedqty" => $selectedqty, "selectedname" => $selname, "selectedoption" => $selectedoption, "selectedsetup" => $selsetup, "selectedrecurring" => $selrecurring, "qtyminimum" => $qtyminimum, "qtymaximum" => $qtymaximum, "options" => $options);
        }
        return $configoptions;
	}
    public static function getCartConfigOptions($pid, $values, $cycle, $accountid = "", $orderform = "", $showHiddenOverride = false)
    {
        global $CONFIG;
        global $_LANG;
        global $currency;
        if (!is_array($currency) || !array_key_exists("id", $currency)) {
            $currency = \App\Helpers\Format::getCurrency();
        }
        $configoptions = array();
        $cycle = strtolower(str_replace(array("-", " "), "", $cycle));
        if ($cycle == "onetime") {
            $cycle = "monthly";
        }
        $showhidden = $showHiddenOverride || Auth::guard('admin')->check() && (defined("ADMINAREA") || defined("APICALL") || (Application::isAdminAreaRequest() || Application::isClientAreaRequest())) ? true : false;
        $cyclemonths = \App\Helpers\Invoice::getBillingCycleMonths($cycle);
        if ($accountid) {
            $values = $options = array();
            $accountid = (int) $accountid;
            $query = "SELECT tblproductconfigoptionssub.id, tblproductconfigoptionssub.configid\nFROM tblproductconfigoptionssub\nINNER JOIN tblproductconfigoptions ON tblproductconfigoptionssub.configid = tblproductconfigoptions.id\nINNER JOIN tblproductconfiglinks ON tblproductconfigoptions.gid = tblproductconfiglinks.gid\nINNER JOIN tblhosting on tblproductconfiglinks.pid = tblhosting.packageid\nWHERE tblhosting.id = " . $accountid . "\nAND tblproductconfigoptions.optiontype IN (3, 4)\nGROUP BY tblproductconfigoptionssub.configid\nORDER BY tblproductconfigoptionssub.sortorder ASC, id ASC;";
            $configOptionsResult = DB::select(DB::raw($query));
            foreach ($configOptionsResult as $configOptionsData) {
                $options[$configOptionsData->id] = $configOptionsData->configid;
            }
            if (count($options)) {
                foreach ($options as $subID => $configOptionID) {
                    $isOptionSaved = (bool) \App\Models\Hostingconfigoption::where(array("configid" => $configOptionID, "relid" => $accountid))->value("configid");
                    if (!$isOptionSaved) {
                        \App\Models\Hostingconfigoption::insert(array("relid" => $accountid, "configid" => $configOptionID, "optionid" => $subID, "qty" => 0));
                    }
                }
            }
            $result = \App\Models\Hostingconfigoption::where(array("relid" => $accountid))->get();
            foreach ($result->toArray() as $data) {
                $configid = $data["configid"];
                $result2 = \App\Models\Productconfigoption::where(array("id" => $configid));
                $data2 = $result2;
                $optiontype = $data2->value("optiontype");
                if ($optiontype == 3 || $optiontype == 4) {
                    $configoptionvalue = $data["qty"];
                } else {
                    $configoptionvalue = $data["optionid"];
                }
                $values[$configid] = $configoptionvalue;
            }
        }
        $where = array("pid" => $pid);
        if (!$showhidden) {
            $where["hidden"] = 0;
        }
        $result2 = \App\Models\Productconfigoption::selectRaw("tblproductconfigoptions.*")
            ->where($where)
            ->orderBy("tblproductconfigoptions.order", "ASC")
            ->orderBy("tblproductconfigoptions.id", "ASC")
            ->join("tblproductconfiglinks", "tblproductconfiglinks.gid","=","tblproductconfigoptions.gid")
            ->get();
        foreach ($result2->toArray() as $data2) {
            $optionid = $data2["id"];
            $optionname = $data2["optionname"];
            $optiontype = $data2["optiontype"];
            $optionhidden = $data2["hidden"];
            $qtyminimum = $data2["qtyminimum"];
            $qtymaximum = $data2["qtymaximum"];
            if (strpos($optionname, "|")) {
                $optionname = explode("|", $optionname);
                $optionname = trim($optionname[1]);
            }
            $options = array();
            $selname = $selectedoption = $selsetup = $selrecurring = "";
            $selectedqty = 0;
            $foundPreselectedValue = false;
            $selvalue = isset($values[$optionid]) ? $values[$optionid] : "";
            if ($optiontype == "3") {
                $result3 = \App\Models\Productconfigoptionssub::where(array("configid" => $optionid));
                $data3 = $result3;
                $opid = $data3->value("id");
                $ophidden = $data3->value("hidden");
                $opname = $data3->value("optionname");
                if (strpos($opname, "|")) {
                    $opname = explode("|", $opname);
                    $opname = trim($opname[1]);
                }
                $opnameonly = $opname;
                $result4 = \App\Models\Pricing::where(array("type" => "configoptions", "currency" => $currency["id"], "relid" => $opid));
                $data = $result4;
                $setup = Database::hasColumn('tblpricing', $cycle) ? ($data->value(substr($cycle, 0, 1) . "setupfee") ?? 0) : 0;
                $price = $fullprice = Database::hasColumn('tblpricing', $cycle) ? ($data->value($cycle) ?? 0) : 0;
                if ($orderform && $CONFIG["ProductMonthlyPricingBreakdown"]) {
                    $price = $price / $cyclemonths;
                }
                if (0 < $price) {
                    $opname .= " " . \App\Helpers\Format::formatCurrency($price);
                }
                $setupvalue = 0 < $setup ? " + " . \App\Helpers\Format::formatCurrency($setup) . " " . $_LANG["ordersetupfee"] : "";
                $options[] = array("id" => $opid, "hidden" => $ophidden, "name" => $opname . $setupvalue, "nameonly" => $opnameonly, "recurring" => $price);
                if (!$selvalue) {
                    $selvalue = 0;
                }
                $selectedqty = $selvalue;
                $selvalue = $opid;
                $selname = $_LANG["no"];
                if ($selectedqty) {
                    $selname = $_LANG["yes"];
                    $selectedoption = $opname;
                    $selsetup = $setup;
                    $selrecurring = $fullprice;
                }
            } else {
                if ($optiontype == "4") {
                    $result3 = \App\Models\Productconfigoptionssub::where(array("configid" => $optionid));
                    $data3 = $result3;
                    $opid = $data3->value("id");
                    $ophidden = $data3->value("hidden");
                    $opname = $data3->value("optionname");
                    if (strpos($opname, "|")) {
                        $opname = explode("|", $opname);
                        $opname = trim($opname[1]);
                    }
                    $opnameonly = $opname;
                    $result4 = \App\Models\Pricing::where(array("type" => "configoptions", "currency" => $currency["id"], "relid" => $opid));
                    $data = $result4;
                    $setup = Database::hasColumn('tblpricing', $cycle) ? ($data->value(substr($cycle, 0, 1) . "setupfee") ?? 0) : 0;
                    $price = $fullprice = Database::hasColumn('tblpricing', $cycle) ? ($data->value($cycle) ?? 0) : 0;
                    if ($orderform && $CONFIG["ProductMonthlyPricingBreakdown"]) {
                        $price = $price / $cyclemonths;
                    }
                    if (0 < $price) {
                        $opname .= " " . \App\Helpers\Format::formatCurrency($price);
                    }
                    $setupvalue = 0 < $setup ? " + " . \App\Helpers\Format::formatCurrency($setup) . " " . $_LANG["ordersetupfee"] : "";
                    $options[] = array("id" => $opid, "hidden" => $ophidden, "name" => $opname . $setupvalue, "nameonly" => $opnameonly, "recurring" => $price);
                    if (!is_numeric($selvalue) || $selvalue < 0) {
                        $selvalue = $qtyminimum;
                    }
                    if (0 < $qtyminimum && $selvalue < $qtyminimum) {
                        $selvalue = $qtyminimum;
                    }
                    if (0 < $qtymaximum && $qtymaximum < $selvalue) {
                        $selvalue = $qtymaximum;
                    }
                    $selectedqty = $selvalue;
                    $selvalue = $opid;
                    $selname = $selectedqty;
                    $selectedoption = $opname;
                    $selsetup = $setup * $selectedqty;
                    $selrecurring = $fullprice * $selectedqty;
                } else {
                    $result3 = \App\Models\Productconfigoptionssub::selectRaw("tblpricing.*,tblproductconfigoptionssub.*",)
                        ->where(array("tblproductconfigoptionssub.configid" => $optionid, "tblpricing.type" => "configoptions", "tblpricing.currency" => $currency["id"]))
                        ->orderBy("tblproductconfigoptionssub.sortorder", "ASC")
                        ->orderBy("tblproductconfigoptionssub.id", "ASC")
                        ->join("tblpricing", "tblpricing.relid","=","tblproductconfigoptionssub.id")
                        ->get();
                    foreach ($result3->toArray() as $data3) {
                        $opid = $data3["id"];
                        $ophidden = $data3["hidden"];
                        $setup = $data3[substr($cycle, 0, 1) . "setupfee"] ?? 0;
                        $price = $fullprice = $data3[$cycle] ?? 0;
                        if ($orderform && $CONFIG["ProductMonthlyPricingBreakdown"]) {
                            $price = $price / $cyclemonths;
                        }
                        $setupvalue = 0 < $setup ? " + " . \App\Helpers\Format::formatCurrency($setup) . " " . $_LANG["ordersetupfee"] : "";
                        $rawName = $required = $opname = $data3["optionname"];
                        if (strpos($opname, "|")) {
                            $opnameArr = explode("|", $opname);
                            $opname = trim($opnameArr[1]);
                            $required = trim($opnameArr[0]);
                            if (defined("APICALL") || Application::isApiRequest()) {
                                $setupvalue = "";
                            }
                        }
                        $opnameonly = $opname;
                        if (0 < $price && (!defined("APICALL") || !Application::isApiRequest())) {
                            $opname .= " " . \App\Helpers\Format::formatCurrency($price);
                        }
                        if ($showhidden || !$ophidden || $opid == $selvalue) {
                            $options[] = array("id" => $opid, "name" => $opname . $setupvalue, "rawName" => $rawName, "required" => $required, "nameonly" => $opnameonly, "nameandprice" => $opname, "setup" => $setup, "fullprice" => $fullprice, "recurring" => $price, "hidden" => $ophidden);
                        }
                        if ($opid == $selvalue || !$selvalue && !$ophidden) {
                            $selname = $opnameonly;
                            $selectedoption = $opname;
                            $selsetup = $setup;
                            $selrecurring = $fullprice;
                            $selvalue = $opid;
                            $foundPreselectedValue = true;
                        }
                    }
                    if (!$foundPreselectedValue && 0 < count($options)) {
                        $selname = $options[0]["nameonly"];
                        $selectedoption = $options[0]["nameandprice"];
                        $selsetup = $options[0]["setup"];
                        $selrecurring = $options[0]["fullprice"];
                        $selvalue = $options[0]["id"];
                    }
                }
            }
            $configoptions[] = array("id" => $optionid, "hidden" => $optionhidden, "optionname" => $optionname, "optiontype" => $optiontype, "selectedvalue" => $selvalue, "selectedqty" => $selectedqty, "selectedname" => $selname, "selectedoption" => $selectedoption, "selectedsetup" => $selsetup, "selectedrecurring" => $selrecurring, "qtyminimum" => $qtyminimum, "qtymaximum" => $qtymaximum, "options" => $options);
        }
        return $configoptions;
    }

    public static function validateAndSanitizeQuantityConfigOptions($configoption)
    {
        // $whmcs = WHMCS\Application::getInstance();
        $validConfigOptions = $errorConfigIDs = array();
        $errorMessage = "";
        foreach ($configoption as $configid => $optionvalue) {
            $data = \App\Models\Productconfigoption::where(array("id" => $configid));
            $optionname = $data->value("optionname");
            $optiontype = $data->value("optiontype");
            $qtyminimum = $data->value("qtyminimum");
            $qtymaximum = $data->value("qtymaximum");
            if (strpos($optionname, "|")) {
                $optionname = explode("|", $optionname);
                $optionname = trim($optionname[1]);
            }
            if ($optiontype == "3") {
                $optionvalue = $optionvalue ? "1" : "0";
            } else {
                if ($optiontype == "4") {
                    $optionvalue = (int) $optionvalue;
                    if ($qtyminimum < 0) {
                        $qtyminimum = 0;
                    }
                    if ($optionvalue < 0 || $optionvalue < $qtyminimum && 0 < $qtyminimum || 0 < $qtymaximum && $qtymaximum < $optionvalue) {
                        if ($qtymaximum <= 0) {
                            $qtymaximum = \Lang::get("client.clientareaunlimited");
                        }
                        $errorMessage .= "<li>" . sprintf(\Lang::get("client.configoptionqtyminmax"), $optionname, $qtyminimum, $qtymaximum);
                        $errorConfigIDs[] = $configid;
                        $optionvalue = 0 < $qtyminimum ? $qtyminimum : 0;
                    }
                } else {
                    $optionvalue = \App\Models\Productconfigoptionssub::where(array("configid" => $configid, "id" => $optionvalue))->value("id");
                    if (!$optionvalue) {
                        $errorMessage .= "<li>The option selected for " . $optionname . " is not valid";
                        $errorConfigIDs[] = $configid;
                    }
                }
            }
            $validConfigOptions[$configid] = $optionvalue;
        }
        return array("validOptions" => $validConfigOptions, "errorConfigIDs" => $errorConfigIDs, "errorMessage" => $errorMessage);
    }
}

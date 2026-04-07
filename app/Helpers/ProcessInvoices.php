<?php
namespace App\Helpers;

use DB, Auth;
// Import Model Class here

// Import Package Class here
use App\Helpers\Cfg;
use App\Helpers\LogActivity;
use App\Helpers\Hooks;
use App\Helpers\Database;

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ProcessInvoices
{
    /**
     * getInvoiceProductDetails
     */
	public static function getInvoiceProductDetails($id, $pid, $regdate, $nextduedate, $billingcycle, $domain, $userid)
    {
        global $CONFIG;
        global $_LANG;
        global $currency;
        $data = \App\Models\Product::where('id', $pid);
        $type = $data->value("type");
        // $clientLanguage = \App\Models\Client::find($userid, array("language"))->language ?: NULL;
        $clientLanguage = \App\Models\Client::where('id', $userid)->value("language");
        $package = \App\Models\Product::getProductName($pid, $data->value("name"), $clientLanguage);
        $tax = $data->value("tax");
        $proratabilling = $data->value("proratabilling");
        $proratadate = $data->value("proratadate");
        $proratachargenextmonth = $data->value("proratachargenextmonth");
        $recurringcycles = $data->value("recurringcycles");
        $userid = \App\Models\Hosting::where(array("id" => $id))->value("userid") ?? 0;
        $currency = \App\Helpers\Format::getCurrency($userid);
        if ($tax && $CONFIG["TaxEnabled"]) {
            $tax = "1";
        } else {
            $tax = "0";
        }
        $paydates = "";
        if ($regdate || $nextduedate) {
            if ($regdate == $nextduedate && $proratabilling) {
                $orderyear = substr($regdate, 0, 4);
                $ordermonth = substr($regdate, 5, 2);
                $orderday = substr($regdate, 8, 2);
                $proratavalues = \App\Helpers\Invoice::getProrataValues($billingcycle, 0, $proratadate, $proratachargenextmonth, $orderday, $ordermonth, $orderyear, $userid);
                $invoicepayuntildate = $proratavalues["invoicedate"];
               
            } else {
                $invoicepayuntildate = \App\Helpers\Invoice::getInvoicePayUntilDate($nextduedate, $billingcycle);
          
            }
            if ($billingcycle != "One Time") {
                $clientHelper = new \App\Helpers\Client();
                $paydates = " (" . $clientHelper->fromMySQLDate($nextduedate) . " - " . $clientHelper->fromMySQLDate($invoicepayuntildate) . ")";
            }
        }
        $description = $package;
        if ($domain) {
            $description .= " - " . $domain;
        }
        $description .= $paydates;
        $configbillingcycle = $billingcycle;
        if ($configbillingcycle == "One Time" || $configbillingcycle == "Free Account") {
            $configbillingcycle = "monthly";
        }
        $configbillingcycle = strtolower(str_replace("-", "", $configbillingcycle));
        $result = DB::table('tblhostingconfigoptions')
            ->select('tblproductconfigoptions.id', 'tblproductconfigoptions.optionname AS confoption', 'tblproductconfigoptions.optiontype AS conftype', 'tblproductconfigoptionssub.optionname', 'tblhostingconfigoptions.qty', 'tblhostingconfigoptions.optionid')
            ->join('tblproductconfigoptions', 'tblproductconfigoptions.id', '=', 'tblhostingconfigoptions.configid')
            ->join('tblproductconfigoptionssub', 'tblproductconfigoptionssub.id', '=', 'tblhostingconfigoptions.optionid')
            ->join('tblhosting', 'tblhosting.id', '=', 'tblhostingconfigoptions.relid')
            ->join('tblproductconfiglinks', 'tblproductconfiglinks.gid', '=', 'tblproductconfigoptions.gid')
            ->where('tblhostingconfigoptions.relid', (int) $id)
            ->where('tblproductconfigoptions.hidden', 0)
            ->where('tblproductconfigoptionssub.hidden', 0)
            ->whereRaw('tblproductconfiglinks.pid=tblhosting.packageid')
            ->orderBy('tblproductconfigoptions.order', 'ASC')
            ->orderBy('tblproductconfigoptions.id', 'ASC')
            ->get()->toArray();

        $result = array_map(function ($value) {
            return (array)$value;
        }, $result);
        foreach ($result as $data) {
            $confoption = $data["confoption"];
            $conftype = $data["conftype"];
            if (strpos($confoption, "|")) {
                $confoption = explode("|", $confoption);
                $confoption = trim($confoption[1]);
            }
            $optionname = $data["optionname"];
            $optionqty = $data["qty"];
            $optionid = $data["optionid"];
            if (strpos($optionname, "|")) {
                $optionname = explode("|", $optionname);
                $optionname = trim($optionname[1]);
            }
            if ($conftype == 3) {
                if ($optionqty) {
                    $optionname = $_LANG["yes"];
                } else {
                    $optionname = $_LANG["no"];
                }
            } else {
                if ($conftype == 4) {
                    $optionname = (string) $optionqty . " x " . $optionname . " ";
                    $qtyprice = \App\Models\Pricing::select($configbillingcycle)->where('type', 'configoptions')->where('currency', $currency["id"])->where('relid', $optionid)->first();
                    $qtyprice = $qtyprice->{$configbillingcycle};
                    $optionname .= \App\Helpers\Format::formatCurrency($qtyprice);
                }
            }
            $description .= "\n" . $confoption . ": " . $optionname;
        }
        $result = \App\Models\Customfield::select("tblcustomfields.id", "tblcustomfields.fieldname", DB::raw("(SELECT value FROM tblcustomfieldsvalues WHERE tblcustomfieldsvalues.fieldid=tblcustomfields.id AND tblcustomfieldsvalues.relid=" . (int) $id . " LIMIT 1) AS value"))->where('type', 'product')->where('relid', $pid)->where('showinvoice', 'on')->get();
        foreach ($result->toArray() as $data) {
            if ($data["value"]) {
                $data["fieldname"] = \App\Models\Customfield::getFieldName($data["id"], $data["fieldname"], $clientLanguage);
                $description .= "\n" . $data["fieldname"] . ": " . $data["value"];
            }
        }
        return array("description" => $description, "tax" => $tax, "recurringcycles" => $recurringcycles);
    }

    /**
     * getInvoiceProductPromo
     */
    public static function getInvoiceProductPromo($amount, $promoid, $userid = "", $serviceid = "", $orderamt = "")
    {
        global $_LANG;
        global $currency;
        if (!$promoid) {
            return array();
        }
        $data = \App\Models\Promotion::find($promoid);
        $data = $data->toArray();
        $promo_id = $data["id"];
        if (!$promo_id) {
            return array();
        }
        $promo_code = $data["code"];
        $promo_type = $data["type"];
        $promo_recurring = $data["recurring"];
        $promo_value = $data["value"];
        $promo_recurfor = $data["recurfor"];
        if ($userid) {
            $currency = \App\Helpers\Format::getCurrency($userid);
        }
        if ($serviceid) {
            $data = \App\Models\Hosting::find($serviceid);
            $data = $data->toArray();
            $pid = $data["packageid"];
            $regdate = $data["regdate"];
            $nextduedate = $data["nextduedate"];
            $firstpaymentamount = $data["firstpaymentamount"];
            $billingcycle = $data["billingcycle"];
            $billingcycle = str_replace("-", "", strtolower($billingcycle));
            if ($billingcycle == "one time") {
                $billingcycle = "monthly";
            }
        }
        if ($serviceid && $promo_recurring && 0 < $promo_recurfor) {
            $promo_recurringcount = \App\Models\Invoiceitem::where('userid', $userid)->where('type', 'Hosting')->where('relid', $serviceid)->count();
            if ($promo_recurfor - 1 <= $promo_recurringcount) {
                $fullAmount = self::getInvoiceProductDefaultPrice($pid, $billingcycle, $regdate, $nextduedate);
                $configoptions = \App\Helpers\ConfigOptions::getCartConfigOptions($pid, "", $billingcycle, $serviceid);
                foreach ($configoptions as $configoption) {
                    $fullAmount += $configoption["selectedrecurring"];
                }
                \App\Models\Hosting::where('id', $serviceid)->update(array("amount" => $fullAmount, "promoid" => "0"));
            }
        }
        if (!$promo_id) {
            return array();
        }
        if (!$serviceid || $promo_recurring || !$promo_recurring && $regdate == $nextduedate) {
            if ($promo_type == "Percentage") {
                if ($promo_value != 100) {
                    $promo_amount = round($amount / (1 - $promo_value / 100), 2) - $amount;
                } else {
                    $promo_amount = 0;
                }
                if ($orderamt) {
                    $promoAmountCheck = $promo_amount + $amount;
                    if ($promoAmountCheck < $orderamt) {
                        $promo_amount = $promo_amount + $orderamt - $promoAmountCheck;
                    }
                }
                if (0 < $promo_value && $promo_amount <= 0) {
                    $promo_amount = $orderamt ? $orderamt : self::getInvoiceProductDefaultPrice($pid, $billingcycle, $regdate, $nextduedate);
                }
                $promo_value .= "%";
            } else {
                if ($promo_type == "Fixed Amount") {
                    if ($currency["id"] != 1) {
                        $promo_value = \App\Helpers\Format::ConvertCurrency($promo_value, 1, $currency["id"]);
                    }
                    $default_price = "";
                    $default_price = self::getInvoiceProductDefaultPrice($pid, $billingcycle, $regdate, $nextduedate, $serviceid, $userid);
                    if ($default_price < $promo_value) {
                        $promo_value = $default_price;
                    }
                    $default_price = "";
                    $promo_amount = $promo_value;
                    $promo_value = \App\Helpers\Format::formatCurrency($promo_value);
                } else {
                    if ($promo_type == "Price Override") {
                        if ($currency["id"] != 1) {
                            $promo_value = \App\Helpers\Format::ConvertCurrency($promo_value, 1, $currency["id"]);
                        }
                        $promo_amount = $orderamt ? $orderamt : self::getInvoiceProductDefaultPrice($pid, $billingcycle, $regdate, $nextduedate);
                        $promo_amount -= $promo_value;
                        $promo_value = \App\Helpers\Format::formatCurrency($promo_value) . " " . $_LANG["orderpromopriceoverride"];
                    } else {
                        if ($promo_type == "Free Setup") {
                            $promo_amount = $orderamt ? $orderamt : self::getInvoiceProductDefaultPrice($pid, $billingcycle, $regdate, $nextduedate);
                            $promo_amount -= $firstpaymentamount;
                            $promo_value = $_LANG["orderpromofreesetup"];
                        }
                    }
                }
            }
            \App\Helpers\Functions::getUsersLang($userid);
            $promo_recurring = $promo_recurring ? $_LANG["recurring"] : $_LANG["orderpaymenttermonetime"];
            $promo_description = $_LANG["orderpromotioncode"] . ": " . $promo_code . " - " . $promo_value . " " . $promo_recurring . " " . $_LANG["orderdiscount"];
            \App\Helpers\Functions::getUsersLang(0);
            return array("description" => $promo_description, "amount" => $promo_amount * -1);
        }
        return array();
    }

    /**
     * getInvoiceProductDefaultPrice
     */
    public static function getInvoiceProductDefaultPrice($pid, $billingCycle, $regDate, $nextDueDate, $serviceID = 0, $userID = 0)
    {
        global $currency;
        $data = DB::table("tblpricing")->where("type", "=", "product")->where("currency", "=", $currency["id"])->where("relid", "=", $pid)->first();
        $amount = 0;
        switch ($billingCycle) {
            case "one time":
            case "monthly":
                $setupFieldName = "msetupfee";
                $amount = $data->monthly;
                break;
            case "quarterly":
                $setupFieldName = "qsetupfee";
                $amount = $data->quarterly;
                break;
            case "semiannually":
                $setupFieldName = "ssetupfee";
                $amount = $data->semiannually;
                break;
            case "annually":
                $setupFieldName = "asetupfee";
                $amount = $data->annually;
                break;
            case "biennially":
                $setupFieldName = "bsetupfee";
                $amount = $data->biennially;
                break;
            case "triennially":
                $setupFieldName = "tsetupfee";
                $amount = $data->triennally;
                break;
            default:
                throw new \Exception("Unable to obtain pricing for billing cycle");
        }
        if ($regDate == $nextDueDate && isset($setupFieldName)) {
            $amount += $data->{$setupFieldName};
        }
        if ($serviceID) {
            if ($billingCycle == "semiannually") {
                $billingCycle = "Semi-Annually";
            } else {
                $billingCycle = ucfirst($billingCycle);
            }
            $includeSetup = false;
            if ($regDate == $nextDueDate) {
                $includeSetup = true;
            }
            $amount = self::recalcRecurringProductPrice($serviceID, $userID, $pid, $billingCycle, "empty", 0, $includeSetup);
        }
        return $amount;
    }

    /**
     * recalcRecurringProductPrice
     */
    public static function recalcRecurringProductPrice($serviceid, $userid = "", $pid = "", $billingcycle = "", $configoptionsrecurring = "empty", $promoid = 0, $includesetup = false)
    {
        if (!$userid || !$pid || !$billingcycle) {
            $result = \App\Models\Hosting::find($serviceid);
            $data = $result->toArray();
            if (!$userid) {
                $userid = $data["userid"];
            }
            if (!$pid) {
                $pid = $data["packageid"];
            }
            if (!$billingcycle) {
                $billingcycle = $data["billingcycle"];
            }
        }
        global $currency;
        $currency = \App\Helpers\Format::getCurrency($userid);
        $result = \App\Models\Pricing::where('type', 'product')->where('currency', $currency["id"])->where('relid', $pid)->first();
        $data = $result->toArray();
        if ($billingcycle == "Monthly") {
            $amount = $data["monthly"];
        } else {
            if ($billingcycle == "Quarterly") {
                $amount = $data["quarterly"];
            } else {
                if ($billingcycle == "Semi-Annually") {
                    $amount = $data["semiannually"];
                } else {
                    if ($billingcycle == "Annually") {
                        $amount = $data["annually"];
                    } else {
                        if ($billingcycle == "Biennially") {
                            $amount = $data["biennially"];
                        } else {
                            if ($billingcycle == "Triennially") {
                                $amount = $data["triennially"];
                            } else {
                                $amount = 0;
                            }
                        }
                    }
                }
            }
        }
        if ($amount <= 0) {
            $amount = 0;
        }
        if ($includesetup === true) {
            $setupvar = substr(strtolower($billingcycle), 0, 1);
            if (0 < $data[$setupvar . "setupfee"]) {
                $amount += $data[$setupvar . "setupfee"];
            }
        }
        if ($configoptionsrecurring == "empty") {
            $configoptions = \App\Helpers\ConfigOptions::getCartConfigOptions($pid, "", $billingcycle, $serviceid);
            foreach ($configoptions as $configoption) {
                $amount += $configoption["selectedrecurring"];
                if ($includesetup === true) {
                    $amount += $configoption["selectedsetup"];
                }
            }
        } else {
            $amount += $configoptionsrecurring;
        }
        if ($promoid) {
            $amount -= self::recalcPromoAmount($pid, $userid, $serviceid, $billingcycle, $amount, $promoid);
        }
        return $amount;
    }

    /**
     * recalcPromoAmount
     */
    public static function recalcPromoAmount($pid, $userid, $serviceid, $billingcycle, $recurringamount, $promoid)
    {
        global $currency;
        $currency = \App\Helpers\Format::getCurrency($userid);
        $recurringdiscount = $used = 0;
        $result = \App\Models\Promotion::find($promoid);
        $data = $result->toArray();
        $id = $data["id"];
        $type = $data["type"];
        $recurring = $data["recurring"];
        $value = $data["value"];
        if ($recurring) {
            if ($type == "Percentage") {
                $recurringdiscount = $recurringamount * $value / 100;
            } else {
                if ($type == "Fixed Amount") {
                    if ($currency["id"] != 1) {
                        $value = \App\Helpers\Format::ConvertCurrency($value, 1, $currency["id"]);
                    }
                    if ($recurringamount < $value) {
                        $recurringdiscount = $recurringamount;
                    } else {
                        $recurringdiscount = $value;
                    }
                } else {
                    if ($type == "Price Override") {
                        if ($currency["id"] != 1) {
                            $value = \App\Helpers\Format::ConvertCurrency($value, 1, $currency["id"]);
                        }
                        $recurringdiscount = $recurringamount - $value;
                    }
                }
            }
        }
        return $recurringdiscount;
    }

    /**
     * createInvoices
     */
    public static function createInvoices($func_userid = "", $noemails = "", $nocredit = "", $specificitems = "", $task = NULL)
    {
        global $CONFIG;
        global $_LANG;
        global $invoicecount;
        global $invoiceid;
        global $continuous_invoicing_active_only;
        $clientLanguage = \App\Models\Client::where('id', $func_userid)->value("language");
        $continvoicegen = Cfg::get("ContinuousInvoiceGeneration");
        $invoicedate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + $CONFIG["CreateInvoiceDaysBefore"], date("Y")));
        $invoicedatemonthly = $CONFIG["CreateInvoiceDaysBeforeMonthly"] ? date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + $CONFIG["CreateInvoiceDaysBeforeMonthly"], date("Y"))) : $invoicedate;
        $invoicedatequarterly = $CONFIG["CreateInvoiceDaysBeforeQuarterly"] ? date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + $CONFIG["CreateInvoiceDaysBeforeQuarterly"], date("Y"))) : $invoicedate;
        $invoicedatesemiannually = $CONFIG["CreateInvoiceDaysBeforeSemiAnnually"] ? date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + $CONFIG["CreateInvoiceDaysBeforeSemiAnnually"], date("Y"))) : $invoicedate;
        $invoicedateannually = $CONFIG["CreateInvoiceDaysBeforeAnnually"] ? date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + $CONFIG["CreateInvoiceDaysBeforeAnnually"], date("Y"))) : $invoicedate;
        $invoicedatebiennially = $CONFIG["CreateInvoiceDaysBeforeBiennially"] ? date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + $CONFIG["CreateInvoiceDaysBeforeBiennially"], date("Y"))) : $invoicedate;
        $invoicedatetriennially = $CONFIG["CreateInvoiceDaysBeforeTriennially"] ? date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + $CONFIG["CreateInvoiceDaysBeforeTriennially"], date("Y"))) : $invoicedate;
        $domaininvoicedate = 0 < Cfg::get("CreateDomainInvoiceDaysBefore") ? date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + $CONFIG["CreateDomainInvoiceDaysBefore"], date("Y"))) : $invoicedate;
        $matchfield = $continvoicegen ? "nextinvoicedate" : "nextduedate";
        Hooks::run_hook("PreInvoicingGenerateInvoiceItems", array());
        $statusfilter = "'Pending','Active'";
        if (!$continuous_invoicing_active_only) {
            $statusfilter .= ",'Suspended'";
        }
        $hostingquery = "domainstatus IN (" . $statusfilter . ") AND billingcycle!='Free' AND billingcycle!='Free Account' AND nextduedate!='00000000' AND nextinvoicedate!='00000000' AND ((billingcycle='Monthly' AND " . $matchfield . "<='" . $invoicedatemonthly . "') OR (billingcycle='Quarterly' AND " . $matchfield . "<='" . $invoicedatequarterly . "') OR (billingcycle='Semi-Annually' AND " . $matchfield . "<='" . $invoicedatesemiannually . "') OR (billingcycle='Annually' AND " . $matchfield . "<='" . $invoicedateannually . "') OR (billingcycle='Biennially' AND " . $matchfield . "<='" . $invoicedatebiennially . "') OR (billingcycle='Triennially' AND " . $matchfield . "<='" . $invoicedatetriennially . "') OR (billingcycle='One Time'))";
        $domainquery = "(donotrenew='' OR `status`='Pending') AND `status` IN (" . $statusfilter . ") AND " . $matchfield . "<='" . $domaininvoicedate . "'";
        $hostingaddonsquery = "tblhostingaddons.billingcycle!='Free' AND tblhostingaddons.billingcycle!='Free Account' AND tblhostingaddons.status IN (" . $statusfilter . ") AND tblhostingaddons.nextduedate!='00000000' AND tblhostingaddons.nextinvoicedate!='00000000' AND ((tblhostingaddons.billingcycle='Monthly' AND tblhostingaddons." . $matchfield . "<='" . $invoicedatemonthly . "') OR (tblhostingaddons.billingcycle='Quarterly' AND tblhostingaddons." . $matchfield . "<='" . $invoicedatequarterly . "') OR (tblhostingaddons.billingcycle='Semi-Annually' AND tblhostingaddons." . $matchfield . "<='" . $invoicedatesemiannually . "') OR (tblhostingaddons.billingcycle='Annually' AND tblhostingaddons." . $matchfield . "<='" . $invoicedateannually . "') OR (tblhostingaddons.billingcycle='Biennially' AND tblhostingaddons." . $matchfield . "<='" . $invoicedatebiennially . "') OR (tblhostingaddons.billingcycle='Triennially' AND tblhostingaddons." . $matchfield . "<='" . $invoicedatetriennially . "') OR (tblhostingaddons.billingcycle='One Time'))";
        $i = 0;
        $billableitemqry = "";
        if ($func_userid != "") {
            $hostingquery .= " AND userid=" . (int) $func_userid;
            $domainquery .= " AND userid=" . (int) $func_userid;
            $hostingaddonsquery .= " AND tblhosting.userid=" . (int) $func_userid;
            $billableitemqry = " AND userid=" . (int) $func_userid;
        }
        if (is_array($specificitems)) {
            $hostingquery = $domainquery = $hostingaddonsquery = "";
            if (isset($specificitems["products"]) && $specificitems["products"]) {
                $hostingquery .= "(id IN (" . Database::db_build_in_array($specificitems["products"]) . ") AND billingcycle!='Free' AND billingcycle!='Free Account')";
            }
            if (isset($specificitems["addons"]) && $specificitems["addons"]) {
                $hostingaddonsquery .= "tblhostingaddons.id IN (" . Database::db_build_in_array($specificitems["addons"]) . ") AND tblhostingaddons.billingcycle!='Free' AND tblhostingaddons.billingcycle!='Free Account'";
            }
            if (isset($specificitems["domains"]) && $specificitems["domains"]) {
                $domainquery .= "id IN (" . Database::db_build_in_array($specificitems["domains"]) . ")";
            }
        }
        $AddonsArray = $AddonSpecificIDs = array();
        $gateways = new \App\Module\Gateway();
        // dd($hostingquery);
        if ($hostingquery) {
            $cancellationreqids = array();
            $result = \App\Models\Cancelrequest::selectRaw("DISTINCT relid")->get();
            foreach ($result->toArray() as $data) {
                $cancellationreqids[] = $data['relid'];
            }
            $result = \App\Models\Hosting::selectRaw("tblhosting.id,tblhosting.userid,tblhosting.nextduedate,tblhosting.nextinvoicedate,tblhosting.billingcycle,tblhosting.regdate,tblhosting.firstpaymentamount,tblhosting.amount,tblhosting.domain,tblhosting.paymentmethod,tblhosting.packageid,tblhosting.promoid,tblhosting.domainstatus")->whereRaw($hostingquery)->orderBy('domain', 'ASC')->get();
            foreach ($result->toArray() as $data) {
                $id = $serviceid = $data["id"];
                if (!in_array($serviceid, $cancellationreqids)) {
                    $userid = $data["userid"];
                    $nextduedate = $data[$matchfield];
                    $billingcycle = $data["billingcycle"];
                    $status = $data["domainstatus"];
                    $num_rows = \App\Models\Invoiceitem::where('userid', $userid)->where('type', "Hosting")->where("relid", $serviceid)->where('duedate', $nextduedate)->count();
                    $contblock = false;
                    if (!$num_rows && $continvoicegen && $status == "Pending") {
                        $num_rows = \App\Models\Invoiceitem::where('userid', $userid)->where('type', "Hosting")->where("relid", $serviceid)->count();
                        $contblock = true;
                    }
                    
                    if ($num_rows == 0) {
                        $regdate = $data["regdate"];
                        $amount = $regdate == $nextduedate ? $data["firstpaymentamount"] : $data["amount"];
                        $domain = $data["domain"];
                        $paymentmethod = $data["paymentmethod"];
                        if (!$paymentmethod || !$gateways->isActiveGateway($paymentmethod)) {
                            $paymentmethod = \App\Helpers\Functions::ensurePaymentMethodIsSet($userid, $id, "tblhosting");
                        }
                        $pid = $data["packageid"];
                        $promoid = $data["promoid"];
                        $productdetails = self::getInvoiceProductDetails($id, $pid, $regdate, $nextduedate, $billingcycle, $domain, $userid);
                        $description = $productdetails["description"];
                        $tax = $productdetails["tax"];
                        $recurringcycles = $productdetails["recurringcycles"];
                        $recurringfinished = false;
                        if ($recurringcycles) {
                            $num_rows3 = \App\Models\Invoiceitem::where('userid', $userid)->where('type', "Hosting")->where("relid", $id)->count();
                            if ($recurringcycles <= $num_rows3) {
                                DB::table("tblhosting")->where("id", "=", $id)->update(array("domainstatus" => "Completed", "completed_date" => \App\Helpers\Carbon::today()->toDateString()));
                                Hooks::run_hook("ServiceRecurringCompleted", array("serviceid" => $id, "recurringinvoices" => $num_rows3));
                                $recurringfinished = true;
                            }
                        }
                        if (!$recurringfinished) {
                            $promovals = self::getInvoiceProductPromo($amount, $promoid, $userid, $id);
                            if (isset($promovals["description"])) {
                                $amount -= $promovals["amount"];
                            }
                            \App\Models\Invoiceitem::insert(array("userid" => $userid, "type" => "Hosting", "relid" => $id, "description" => $description, "amount" => $amount, "taxed" => $tax, "duedate" => $nextduedate, "paymentmethod" => $paymentmethod,"notes" => "hostingid:$serviceid"));
                            self::cancelUnpaidUpgrade((int) $id);
                            if (isset($promovals["description"])) {
                                \App\Models\Invoiceitem::insert(array("userid" => $userid, "type" => "PromoHosting", "relid" => $id, "description" => $promovals["description"], "amount" => $promovals["amount"], "taxed" => $tax, "duedate" => $nextduedate, "paymentmethod" => $paymentmethod, "notes" => "hostingid:$serviceid"));
                            }
                        }
                    } else {
                        if (!$contblock && $continvoicegen && $billingcycle != "One Time") {
                            \App\Models\Hosting::where('id', $id)->update(array("nextinvoicedate" => \App\Helpers\Invoice::getInvoicePayUntilDate($nextduedate, $billingcycle, true)));
                        }
                    }
                }
                if ($hostingaddonsquery) {
                    $result3 = \App\Models\Hostingaddon::selectRaw("tblhostingaddons.*,tblhostingaddons.regdate AS addonregdate,tblhosting.userid,tblhosting.domain")->whereRaw($hostingaddonsquery . " AND tblhostingaddons.hostingid='" . $id . "'")->orderBy("tblhostingaddons.name", "ASC")->join("tblhosting", "tblhosting.id", "=", "tblhostingaddons.hostingid")->get();
                    foreach ($result3->toArray() as $data) {
                        $id = $data["id"];
                        $userid = $data["userid"];
                        $nextduedate = $data[$matchfield];
                        $status = $data["status"];
                        $num_rows = \App\Models\Invoiceitem::where('userid', $userid)->where('type', "Addon")->where("relid", $id)->where("duedate", $nextduedate)->count();
                        $contblock = false;
                        if (!$num_rows && $continvoicegen && $status == "Pending") {
                            $num_rows = \App\Models\Invoiceitem::where('userid', $userid)->where('type', "Addon")->where("relid", $id)->count();
                            $contblock = true;
                        }
                        if ($num_rows == 0) {
                            $hostingid = $serviceid = $data["hostingid"];
                            $addonid = $data["addonid"];
                            $domain = $data["domain"];
                            $regdate = $data["addonregdate"];
                            $name = $data["name"];
                            $setupfee = $data["setupfee"];
                            $amount = $data["recurring"];
                            $paymentmethod = $data["paymentmethod"];
                            $billingcycle = $data["billingcycle"];
                            $tax = $data["tax"];
                            if (!$name) {
                                if (isset($AddonsArray[$addonid])) {
                                    $name = $AddonsArray[$addonid];
                                } else {
                                    $AddonsArray[$addonid] = $name = \App\Models\Addon::select('name')->where('id', $addonid)->value('name');
                                }
                            }
                            if (!$paymentmethod || !$gateways->isActiveGateway($paymentmethod)) {
                                $paymentmethod = \App\Helpers\Functions::ensurePaymentMethodIsSet($userid, $id, "tblhostingaddons");
                            }
                            $tax = $CONFIG["TaxEnabled"] && $tax ? "1" : "0";
                            $invoicepayuntildate = \App\Helpers\Invoice::getInvoicePayUntilDate($nextduedate, $billingcycle);
                            $paydates = "";
                            if ($billingcycle != "One Time") {
                                $paydates = "(" . (new \App\Helpers\Functions())->fromMySQLDate($nextduedate) . " - " . (new \App\Helpers\Functions())->fromMySQLDate($invoicepayuntildate) . ")";
                            }
                            $num_rows = \App\Models\Invoiceitem::where('userid', $userid)->where('type', "Addon")->where("relid", $id)->where("duedate", $nextduedate)->count();
                            if ($num_rows == 0) {
                                if (!in_array($serviceid, $cancellationreqids)) {
                                    if ($regdate == $nextduedate) {
                                        $amount = $amount + $setupfee;
                                    }
                                    if ($domain) {
                                        $domain = "(" . $domain . ") ";
                                    }
                                    $description = $_LANG["orderaddon"] . " " . $domain . "- " . $name . " " . $paydates;
                                    // NEWFEATURE: customfield addons
                                    $result = \App\Models\Customfield::select("tblcustomfields.id", "tblcustomfields.fieldname", DB::raw("(SELECT value FROM tblcustomfieldsvalues WHERE tblcustomfieldsvalues.fieldid=tblcustomfields.id AND tblcustomfieldsvalues.relid=" . (int) $id . " LIMIT 1) AS value"))->where('type', 'addon')->where('relid', $addonid)->where('showinvoice', 'on')->get();
                                    foreach ($result->toArray() as $datares) {
                                        if ($datares["value"]) {
                                            $datares["fieldname"] = \App\Models\Customfield::getFieldName($datares["id"], $datares["fieldname"], $clientLanguage);
                                            $description .= "\n" . $datares["fieldname"] . ": " . $datares["value"];
                                        }
                                    }
                                    \App\Models\Invoiceitem::insert(array("userid" => $userid, "type" => "Addon", "relid" => $id, "description" => $description, "amount" => $amount, "taxed" => $tax, "duedate" => $nextduedate, "paymentmethod" => $paymentmethod));
                                    $AddonSpecificIDs[] = $id;
                                }
                            } else {
                                if (!$contblock && $continvoicegen) {
                                    \App\Models\Hostingaddon::where('id', $id)->update(array("nextinvoicedate" => \App\Helpers\Invoice::getInvoicePayUntilDate($nextduedate, $billingcycle, true)));
                                }
                            }
                        }
                    }
                }
            }
        }
        if ($hostingaddonsquery) {
            if (count($AddonSpecificIDs)) {
                $hostingaddonsquery .= " AND tblhostingaddons.id NOT IN (" . Database::db_build_in_array($AddonSpecificIDs) . ")";
            }
            $cancellationreqids = array();
            $result = \App\Models\Cancelrequest::selectRaw("DISTINCT relid")->get();
            foreach ($result->toArray() as $data) {
                $cancellationreqids[] = $data['relid'];
            }
            $result = \App\Models\Hostingaddon::selectRaw("tblhostingaddons.*,tblhostingaddons.regdate AS addonregdate,tblhosting.userid,tblhosting.domain")->whereRaw($hostingaddonsquery)->orderBy("tblhostingaddons.name", "ASC")->join("tblhosting", "tblhosting.id", "=", "tblhostingaddons.hostingid")->get();
            foreach ($result->toArray() as $data) {
                $id = $data["id"];
                $userid = $data["userid"];
                $nextduedate = $data[$matchfield];
                $status = $data["status"];
                $num_rows = \App\Models\Invoiceitem::where('userid', $userid)->where('type', "Addon")->where("relid", $id)->where('duedate', $nextduedate)->count();
                $contblock = false;
                if (!$num_rows && $continvoicegen && $status == "Pending") {
                    $num_rows = \App\Models\Invoiceitem::where('userid', $userid)->where('type', "Addon")->where("relid", $id)->count();
                    $contblock = true;
                }
                if ($num_rows == 0) {
                    $hostingid = $serviceid = $data["hostingid"];
                    $addonid = $data["addonid"];
                    $domain = $data["domain"];
                    $regdate = $data["addonregdate"];
                    $name = $data["name"];
                    $setupfee = $data["setupfee"];
                    $amount = $data["recurring"];
                    $paymentmethod = $data["paymentmethod"];
                    if (!$paymentmethod || !$gateways->isActiveGateway($paymentmethod)) {
                        $paymentmethod = \App\Helpers\Functions::ensurePaymentMethodIsSet($userid, $id, "tblhostingaddons");
                    }
                    $billingcycle = $data["billingcycle"];
                    $tax = $data["tax"];
                    if (!$name) {
                        if (isset($AddonsArray[$addonid]) && $AddonsArray[$addonid]) {
                            $name = $AddonsArray[$addonid];
                        } else {
                            $AddonsArray[$addonid] = $name = \App\Models\Addon::select('name')->where('id', $addonid)->value('name') ?? "";
                        }
                    }
                    $tax = $CONFIG["TaxEnabled"] && $tax ? "1" : "0";
                    $invoicepayuntildate = \App\Helpers\Invoice::getInvoicePayUntilDate($nextduedate, $billingcycle);
                    $paydates = "";
                    if ($billingcycle != "One Time") {
                        $paydates = "(" . (new \App\Helpers\Functions())->fromMySQLDate($nextduedate) . " - " . (new \App\Helpers\Functions())->fromMySQLDate($invoicepayuntildate) . ")";
                    }
                    if (!in_array($serviceid, $cancellationreqids)) {
                        if ($regdate == $nextduedate) {
                            $amount = $amount + $setupfee;
                        }
                        if ($domain) {
                            $domain = "(" . $domain . ") ";
                        }
                        $description = $_LANG["orderaddon"] . " " . $domain . "- " . $name . " " . $paydates;
                        // NEWFEATURE: customfield addons
                        $result = \App\Models\Customfield::select("tblcustomfields.id", "tblcustomfields.fieldname", DB::raw("(SELECT value FROM tblcustomfieldsvalues WHERE tblcustomfieldsvalues.fieldid=tblcustomfields.id AND tblcustomfieldsvalues.relid=" . (int) $id . " LIMIT 1) AS value"))->where('type', 'addon')->where('relid', $addonid)->where('showinvoice', 'on')->get();
                        foreach ($result->toArray() as $datares) {
                            if ($datares["value"]) {
                                $datares["fieldname"] = \App\Models\Customfield::getFieldName($datares["id"], $datares["fieldname"], $clientLanguage);
                                $description .= "\n" . $datares["fieldname"] . ": " . $datares["value"];
                            }
                        }
                        $sslCompetitiveUpgradeAddons = \Session::get("SslCompetitiveUpgradeAddons");
                        if (is_array($sslCompetitiveUpgradeAddons) && in_array($id, $sslCompetitiveUpgradeAddons)) {
                            $description .= "<br><small>" . \Lang::get("store.ssl.competitiveUpgradeQualified") . "</small>";
                            array_flip($sslCompetitiveUpgradeAddons);
                            unset($sslCompetitiveUpgradeAddons[$id]);
                            array_flip($sslCompetitiveUpgradeAddons);
                            session()->put("SslCompetitiveUpgradeAddons", $sslCompetitiveUpgradeAddons);
                        }
                        \App\Models\Invoiceitem::insert(array("userid" => $userid, "type" => "Addon", "relid" => $id, "description" => $description, "amount" => $amount, "taxed" => $tax, "duedate" => $nextduedate, "paymentmethod" => $paymentmethod));
                    }
                } else {
                    if (!$contblock && $continvoicegen) {
                        \App\Models\Hostingaddon::where('id', $id)->update(array("nextinvoicedate" => \App\Helpers\Invoice::getInvoicePayUntilDate($nextduedate, $billingcycle, true)));
                    }
                }
            }
        }
        if ($domainquery) {
            $result = \App\Models\Domain::whereRaw($domainquery)->orderBy('domain', 'ASC')->get();
            foreach ($result->toArray() as $data) {
                $id = $data["id"];
                $userid = $data["userid"];
                $nextduedate = $data[$matchfield];
                $status = $data["status"];
                $num_rows = \App\Models\Invoiceitem::where('userid', $userid)->whereIn('type', ['Domain','DomainRegister','DomainTransfer'])->where("relid", $id)->where('duedate', $nextduedate)->count();
                $contblock = false;
                if (!$num_rows && $continvoicegen && $status == "Pending") {
                    $num_rows = \App\Models\Invoiceitem::where('userid', $userid)->whereIn('type', ['Domain','DomainRegister','DomainTransfer'])->where("relid", $id)->count();
                    $contblock = true;
                }
                if ($num_rows == 0) {
                    $type = $data["type"];
                    $domain = $data["domain"];
                    $registrationperiod = $data["registrationperiod"];
                    $regdate = $data["registrationdate"];
                    $expirydate = $data["expirydate"];
                    $paymentmethod = $data["paymentmethod"];
                    if (!$paymentmethod || !$gateways->isActiveGateway($paymentmethod)) {
                        $paymentmethod = \App\Helpers\Functions::ensurePaymentMethodIsSet($userid, $id, "tbldomains");
                    }
                    $dnsmanagement = $data["dnsmanagement"];
                    $emailforwarding = $data["emailforwarding"];
                    $idprotection = $data["idprotection"];
                    $promoid = $data["promoid"];
                    \App\Helpers\Functions::getUsersLang($userid);
                    if ($expirydate == "0000-00-00") {
                        $expirydate = $nextduedate;
                    }
                    // dd($nextduedate);
                    // dd($regdate);
                    if ($regdate == $nextduedate) {
                        $amount = $data["firstpaymentamount"];
                        if ($type == "Transfer") {
                            $domaindesc = $_LANG["domaintransfer"];
                        } else {
                            $domaindesc = $_LANG["domainregistration"];
                            $type = "Register";
                        }
                    } else {
                        $amount = $data["recurringamount"];
                        $domaindesc = $_LANG["domainrenewal"] ?? __('client.domainrenewal');
                        $type = "";
                    }
                    $tax = $CONFIG["TaxEnabled"] && $CONFIG["TaxDomains"] ? "1" : "0";
                    $domaindesc .= " - " . $domain . " - " . $registrationperiod . " " . $_LANG["orderyears"];
                    if ($type != "Transfer") {
                        $domaindesc .= " (" . (new \App\Helpers\Functions())->fromMySQLDate($expirydate) . " - " . (new \App\Helpers\Functions())->fromMySQLDate(\App\Helpers\Invoice::getInvoicePayUntilDate($expirydate, $registrationperiod)) . ")";
                    }
                    if ($dnsmanagement) {
                        $domaindesc .= "\n + " . $_LANG["domaindnsmanagement"];
                    }
                    if ($emailforwarding) {
                        $domaindesc .= "\n + " . $_LANG["domainemailforwarding"];
                    }
                    if ($idprotection) {
                        $domaindesc .= "\n + " . $_LANG["domainidprotection"];
                    }
                    $promo_description = $promo_amount = 0;
                    if ($promoid) {
                        $data = \App\Models\Promotion::where('id', $promoid)->first();
                        $data = $data->toArray();
                        $promo_id = $data["id"];
                        if ($promo_id) {
                            $promo_code = $data["code"];
                            $promo_type = $data["type"];
                            $promo_recurring = $data["recurring"];
                            $promo_value = $data["value"];
                            if ($promo_recurring || !$promo_recurring && $regdate == $nextduedate) {
                                if ($promo_type == "Percentage") {
                                    $promo_amount = round($amount / (1 - $promo_value / 100), 2) - $amount;
                                    $promo_value .= "%";
                                } else {
                                    if ($promo_type == "Fixed Amount") {
                                        $promo_amount = $promo_value;
                                        $currency = \App\Helpers\Format::getCurrency($userid);
                                        $promo_value = \App\Helpers\Format::formatCurrency($promo_value);
                                    }
                                }
                                $amount += $promo_amount;
                                $promo_recurring = $promo_recurring ? $_LANG["recurring"] : $_LANG["orderpaymenttermonetime"];
                                $promo_description = $_LANG["orderpromotioncode"] . ": " . $promo_code . " - " . $promo_value . " " . $promo_recurring . " " . $_LANG["orderdiscount"];
                                $promo_amount *= -1;
                            }
                        }
                    }
                    // dd($domaindesc);
                    \App\Models\Invoiceitem::insert(array("userid" => $userid, "type" => "Domain" . $type, "relid" => $id, "description" => $domaindesc, "amount" => $amount, "taxed" => $tax, "duedate" => $nextduedate, "paymentmethod" => $paymentmethod));
                    if ($promo_description) {
                        \App\Models\Invoiceitem::insert(array("userid" => $userid, "type" => "PromoDomain", "relid" => $id, "description" => $promo_description, "amount" => $promo_amount, "taxed" => $tax, "duedate" => $nextduedate, "paymentmethod" => $paymentmethod));
                    }
                } else {
                    if (!$contblock && $continvoicegen) {
                        $year = substr($nextduedate, 0, 4);
                        $month = substr($nextduedate, 5, 2);
                        $day = substr($nextduedate, 8, 2);
                        $registrationperiod = $data["registrationperiod"];
                        $new_time = mktime(0, 0, 0, $month, $day, $year + $registrationperiod);
                        $nextinvoicedate = date("Y-m-d", $new_time);
                        \App\Models\Domain::where('id', $id)->update(array("nextinvoicedate" => $nextinvoicedate));
                    }
                }
                \App\Helpers\Functions::getUsersLang(0);
            }
        }
        if (!is_array($specificitems)) {
            $billableitemstax = $CONFIG["TaxEnabled"] && $CONFIG["TaxBillableItems"] ? "1" : "0";
            $result = \App\Models\Billableitem::whereRaw("((invoiceaction='1' AND invoicecount='0') OR (invoiceaction='3' AND invoicecount='0' AND duedate<='" . $invoicedate . "') OR (invoiceaction='4' AND duedate<='" . $invoicedate . "' AND (recurfor='0' OR invoicecount<recurfor)))" . $billableitemqry)->get();
            foreach ($result->toArray() as $data) {
                $paymentmethod = \App\Helpers\Gateway::getClientsPaymentMethod($data["userid"]);
                if ($data["invoiceaction"] != "4") {
                    \App\Models\Invoiceitem::insert(array("userid" => $data["userid"], "type" => "Item", "relid" => $data["id"], "description" => $data["description"], "amount" => $data["amount"], "taxed" => $billableitemstax, "duedate" => $data["duedate"], "paymentmethod" => $paymentmethod));
                }
                // TODO: invoicecount
                $updatearray = array("invoicecount" => DB::raw("invoicecount + 1"));
                if ($data["invoiceaction"] == "4") {
                    $num_rows = \App\Models\Invoiceitem::where('type', "Item")->where("relid", $data["id"])->where('duedate', $data["duedate"])->count();
                    if ($num_rows == 0) {
                        \App\Models\Invoiceitem::insert(array("userid" => $data["userid"], "type" => "Item", "relid" => $data["id"], "description" => $data["description"], "amount" => $data["amount"], "taxed" => $billableitemstax, "duedate" => $data["duedate"], "paymentmethod" => $paymentmethod));
                    }
                    $adddays = $addmonths = $addyears = 0;
                    if ($data["recurcycle"] == "Days") {
                        $adddays = $data["recur"];
                    } else {
                        if ($data["recurcycle"] == "Weeks") {
                            $adddays = $data["recur"] * 7;
                        } else {
                            if ($data["recurcycle"] == "Months") {
                                $addmonths = $data["recur"];
                            } else {
                                if ($data["recurcycle"] == "Years") {
                                    $addyears = $data["recur"];
                                }
                            }
                        }
                    }
                    $year = substr($data["duedate"], 0, 4);
                    $month = substr($data["duedate"], 5, 2);
                    $day = substr($data["duedate"], 8, 2);
                    $updatearray["duedate"] = date("Y-m-d", mktime(0, 0, 0, $month + $addmonths, $day + $adddays, $year + $addyears));
                }
                \App\Models\Billableitem::where('id', $data["id"])->update($updatearray);
            }
        }
        Hooks::run_hook("AfterInvoicingGenerateInvoiceItems", array());
        $invoicecount = $invoiceid = 0;
        $where = array();
        $where[] = "invoiceid=0";
        if ($func_userid) {
            $where[] = "userid=" . (int) $func_userid;
        }
        if (!is_array($specificitems)) {
            $where[] = "tblclients.separateinvoices='0'";
            $where[] = "(tblclientgroups.separateinvoices='0' OR tblclientgroups.separateinvoices = '' OR tblclientgroups.separateinvoices is null)";
        }
        // GOD HELP ME
        $result = \App\Models\Invoiceitem::selectRaw("tblinvoiceitems.userid,tblinvoiceitems.duedate,tblinvoiceitems.paymentmethod,tblinvoiceitems.notes")
        ->whereRaw(implode(" AND ", $where))
        ->join("tblclients", "tblclients.id", "=", "tblinvoiceitems.userid")
        ->leftJoin("tblclientgroups", "tblclientgroups.id", "=", "tblclients.groupid")
        ->groupBy("tblinvoiceitems.notes")
        ->orderBy("duedate", "ASC")
        ->get();
        
        // dd($result->toArray());
        
        
        foreach ($result->toArray() as $data) {
            self::createInvoicesProcess($data, $noemails, $nocredit);
        }
        if (!is_array($specificitems)) {
            $where = array();
            $where[] = "invoiceid=0";
            if ($func_userid) {
                $where[] = "userid=" . (int) $func_userid;
            }
            $where[] = "(tblclients.separateinvoices='on' OR tblclients.separateinvoices='1' OR tblclientgroups.separateinvoices='on')";
            $result = \App\Models\Invoiceitem::selectRaw("tblinvoiceitems.id,tblinvoiceitems.userid,tblinvoiceitems.type,tblinvoiceitems.relid,tblinvoiceitems.duedate,tblinvoiceitems.paymentmethod")
                ->whereRaw(implode(" AND ", $where))
                ->join("tblclients", "tblclients.id", "=", "tblinvoiceitems.userid")
                ->leftJoin("tblclientgroups", "tblclientgroups.id", "=", "tblclients.groupid")
                ->orderBy("duedate", "ASC")
                ->get();
            foreach ($result->toArray() as $data) {
                self::createInvoicesProcess($data, $noemails, $nocredit);
            }
        }
        if ($task) {
            $task->output("invoice.created")->write($invoicecount);
        }
        $GLOBALS['invoicecount'] = $invoicecount;
        if ($func_userid) {
            return $invoiceid;
        }
    }

    /**
     * cancelUnpaidUpgrade
     */
    public static function cancelUnpaidUpgrade($serviceId)
    {
        if (empty($serviceId) || !is_int($serviceId)) {
            return false;
        }
        static $cancelledStatuses = NULL;
        if (!is_array($cancelledStatuses)) {
            $cancelledStatuses = DB::table("tblorderstatuses")->where("showcancelled", 1)->pluck("title");
            $cancelledStatuses[] = "Fraud";
        }
        $upgrades = DB::table("tblupgrades")->leftJoin("tblorders", "tblorders.id", "=", "tblupgrades.orderid")->where("tblupgrades.relid", "=", $serviceId)->where("tblupgrades.paid", "=", "N")->whereNotIn("tblorders.status", $cancelledStatuses)->get();
        foreach ($upgrades as $upgrade) {
            \App\Helpers\Orders::changeOrderStatus($upgrade->orderid, "Cancelled");
            $extraData = array(
                "order_id" => $upgrade->orderid,
                "order_number" => \App\Models\Order::select("ordernum")->where("id", $upgrade->orderid)->value("ordernum"),
                "upgrade_type" => $upgrade->type,
                "order_date" => (new \App\Helpers\Functions())->fromMySQLDate($upgrade->date, "", true),
                "order_amount" => \App\Helpers\Format::formatCurrency($upgrade->amount),
                "recurring_amount_change" => \App\Helpers\Format::formatCurrency($upgrade->recurringchange),
            );
            \App\Helpers\Functions::sendMessage("Upgrade Order Cancelled", $serviceId, $extraData);
        }
        return true;
    }

    /**
     * createInvoicesProcess
     */
    public static function createInvoicesProcess($data, $noemails = "", $nocredit = "")
    {
        global $CONFIG;
        global $_LANG;
        global $invoicecount;
        global $invoiceid;
        $itemid = $data["id"] ?? "";
        $userid = $data["userid"];
        $type = $data["type"] ?? "";
        $relid = $data["relid"] ?? 0;
        $duedate = $data["duedate"];
        $notes = $data["notes"];
        $paymentmethod = $invpaymentmethod = $data["paymentmethod"];
        $gateways = new \App\Module\Gateway();
        if (!$invpaymentmethod || !$gateways->isActiveGateway($invpaymentmethod)) {
            $invpaymentmethod = \App\Helpers\Functions::ensurePaymentMethodIsSet($userid, $itemid, "tblinvoiceitems");
        }
        $where = array("userid" => $userid, "duedate" => $duedate, "paymentmethod" => $paymentmethod, "invoiceid" => "0");
        if (!empty($itemid)) {
            $where["id"] = $itemid;
        }
        if (is_null(\App\Models\Invoiceitem::select('id')->where($where)->value("id"))) {
            return false;
        }
        unset($where);
        $invoice = \App\Models\Invoice::newInvoice($userid, $invpaymentmethod);
        $invoice->duedate = $duedate;
        $invoice->setStatusUnpaid();
        $invoice->save();
        $invoiceid = $invoice->id;
        if ($paymentmethod != $invpaymentmethod) {
            LogActivity::Save(sprintf("Invalid payment method updated on invoice generation from '%s' to '%s' for Invoice ID: %d", $paymentmethod, $invpaymentmethod, $invoiceid), $userid);
        }
        if ($itemid) {
            \App\Models\Invoiceitem::where(array("invoiceid" => "0", "userid" => $userid, "type" => "Promo" . $type, "relid" => $relid,"notes"=>$notes))->update(array("invoiceid" => $invoiceid));
            $where = array("id" => $itemid);
        } else {
            $where = array("invoiceid" => "","notes"=>$notes, "duedate" => $duedate, "userid" => $userid, "paymentmethod" => $paymentmethod);
        }
        \App\Models\Invoiceitem::where($where)->update(array("invoiceid" => $invoiceid));
        LogActivity::Save("Created Invoice - Invoice ID: " . $invoiceid, $userid);
        $billableitemstax = $CONFIG["TaxEnabled"] && $CONFIG["TaxCustomInvoices"] ? "1" : "0";
        $result2 = \App\Models\Billableitem::where(array("userid" => $userid, "invoiceaction" => "2", "invoicecount" => "0"))->get();
        foreach ($result2->toArray() as $data) {
            \App\Models\Invoiceitem::insert(array("invoiceid" => $invoiceid, "userid" => $userid, "type" => "Item", "relid" => $data["id"], "description" => $data["description"], "amount" => $data["amount"], "taxed" => $billableitemstax));
            $b = \App\Models\Billableitem::find($data["id"]);
            $b->increment("invoicecount", 1);
        }
        \App\Helpers\Invoice::updateInvoiceTotal($invoiceid);
        $data2 = \App\Models\Client::where('id', $userid);
        $credit = $data2->value('credit');
        $groupid = $data2->value('groupid');
        $data2 = \App\Models\Invoice::where('id', $invoiceid);
        $subtotal = $data2->value("subtotal");
        $total = $data2->value("total");
        $invoiceLineItems = DB::table("tblinvoiceitems")->where("invoiceid", $invoiceid)->get();
        $isaddfundsinvoice = 0 < count(array_filter($invoiceLineItems->toArray(), function ($lineItem) {
            return (bool) in_array($lineItem->type, array("AddFunds", "Invoice"));
        }));
        if ($groupid && !$isaddfundsinvoice) {
            $discountPercent = \App\Models\Clientgroup::where('id', $groupid)->value('discountpercent');
            if (0 < $discountPercent) {
                foreach ($invoiceLineItems as $lineItem) {
                    $discountAmount = $lineItem->amount * $discountPercent / 100 * -1;
                    \App\Models\Invoiceitem::insert(array("invoiceid" => $invoiceid, "userid" => $userid, "type" => "GroupDiscount", "description" => $_LANG["clientgroupdiscount"] . " - " . $lineItem->description, "amount" => $discountAmount, "taxed" => $lineItem->taxed));
                }
                \App\Helpers\Invoice::updateInvoiceTotal($invoiceid);
                $data2 = \App\Models\Client::where('id', $userid);
                $credit = $data2->value('credit');
                $groupid = $data2->value('groupid');
                $data2 = \App\Models\Invoice::where('id', $invoiceid);
                $subtotal = $data2->value("subtotal");
                $total = $data2->value("total");
            }
        }
        if (Cfg::getValue("ContinuousInvoiceGeneration")) {
            $result2 = \App\Models\Invoiceitem::where('invoiceid', $invoiceid)->get();
            foreach ($result2->toArray() as $data) {
                $type = $data["type"];
                $relid = $data["relid"];
                $nextinvoicedate = $data["duedate"];
                $year = substr($nextinvoicedate, 0, 4);
                $month = substr($nextinvoicedate, 5, 2);
                $day = substr($nextinvoicedate, 8, 2);
                $proratabilling = false;
                $regdate = "";
                $nextduedate = "";
                $billingcycle = "";
                if ($type == "Hosting") {
                    $data = \App\Models\Hosting::where(array("id" => $relid));
                    $billingcycle = $data->value("billingcycle");
                    $packageid = $data->value("packageid");
                    $regdate = $data->value("regdate");
                    $nextduedate = $data->value("nextduedate");
                    $data = \App\Models\Product::where(array("id" => $packageid));
                    $proratabilling = $data->value("proratabilling");
                    $proratadate = $data->value("proratadate");
                    $proratachargenextmonth = $data->value("proratachargenextmonth");
                    $proratamonths = \App\Helpers\Invoice::getBillingCycleMonths($billingcycle);
                    $nextinvoicedate = date("Y-m-d", mktime(0, 0, 0, (int) $month + (int) $proratamonths, $day, $year));
                } else {
                    if ($type == "Domain" || $type == "DomainRegister" || $type == "DomainTransfer") {
                        $data = \App\Models\Domain::where(array("id" => $relid));
                        $registrationperiod = $data->value("registrationperiod");
                        $nextduedate = explode("-", $data->value("nextduedate"));
                        $billingcycle = "";
                        $nextinvoicedate = date("Y-m-d", mktime(0, 0, 0, (int) $nextduedate[1], (int) $nextduedate[2], (int) $nextduedate[0] + (int) $registrationperiod));
                    } else {
                        if ($type == "Addon") {
                            $billingcycle = \App\Models\Hostingaddon::select("billingcycle")->where(array("id" => $relid))->value("billingcycle");
                            $proratamonths = \App\Helpers\Invoice::getBillingCycleMonths($billingcycle);
                            $nextinvoicedate = date("Y-m-d", mktime(0, 0, 0, $month + $proratamonths, $day, $year));
                        }
                    }
                }
                if ($billingcycle == "One Time") {
                    $nextinvoicedate = "0000-00-00";
                }
                if ($regdate == $nextduedate && $proratabilling) {
                    if ($billingcycle != "Monthly") {
                        $proratachargenextmonth = 0;
                    }
                    $orderyear = substr($regdate, 0, 4);
                    $ordermonth = substr($regdate, 5, 2);
                    $orderday = substr($regdate, 8, 2);
                    if ($orderday < $proratadate) {
                        $proratamonth = $ordermonth;
                    } else {
                        $proratamonth = $ordermonth + 1;
                    }
                    $days = (strtotime(date("Y-m-d", mktime(0, 0, 0, $proratamonth, $proratadate, $orderyear))) - strtotime(date("Y-m-d"))) / (60 * 60 * 24);
                    $totaldays = 30;
                    $nextinvoicedate = date("Y-m-d", mktime(0, 0, 0, $proratamonth, $proratadate, $orderyear));
                    if ($proratachargenextmonth <= $orderday && $days < 31) {
                        $nextinvoicedate = date("Y-m-d", mktime(0, 0, 0, $proratamonth + $proratamonths, $proratadate, $orderyear));
                    }
                }
                if ($type == "Hosting") {
                    \App\Models\Hosting::where(array("id" => $relid))->update(array("nextinvoicedate" => $nextinvoicedate));
                } else {
                    if ($type == "Domain" || $type == "DomainRegister" || $type == "DomainTransfer") {
                        \App\Models\Domain::where(array("id" => $relid))->update(array("nextinvoicedate" => $nextinvoicedate));
                    } else {
                        if ($type == "Addon") {
                            \App\Models\Hostingaddon::where(array("id" => $relid))->update(array("nextinvoicedate" => $nextinvoicedate));
                        }
                    }
                }
            }
        }
        $doprocesspaid = false;
        $inShoppingCart = defined("SHOPPING_CART");
        if (!$nocredit && $credit != "0.00" && ($inShoppingCart && Request::get("applycredit") || !$inShoppingCart && !Cfg::getValue("NoAutoApplyCredit"))) {
            if ($total <= $credit) {
                $creditleft = $credit - $total;
                $credit = $total;
                $doprocesspaid = true;
            } else {
                $creditleft = 0;
            }
            if (!$inShoppingCart) {
                LogActivity::Save("Credit Automatically Applied at Invoice Creation - Invoice ID: " . $invoiceid . " - Amount: " . $credit, $userid);
            } else {
                LogActivity::Save("Credit Applied at Client Request on Checkout - Invoice ID: " . $invoiceid . " - Amount: " . $credit, $userid);
            }
            \App\Models\Credit::insert(array("clientid" => $userid, "date" => \Carbon\Carbon::now(), "description" => "Credit Applied to Invoice #" . $invoiceid, "amount" => $credit * -1));
            \App\Models\Client::where(array("id" => $userid))->update(array("credit" => $creditleft));
            \App\Models\Invoice::where(array("id" => $invoiceid))->update(array("credit" => $credit));
            \App\Helpers\Invoice::updateInvoiceTotal($invoiceid);
        }
        $invoice = \App\Models\Invoice::find($invoiceid);
        $invoice->save();
        $invoiceArr = array("source" => "autogen", "user" => Auth::guard('admin')->user() ? Auth::guard('admin')->user()->id : "system", "invoiceid" => $invoiceid, "status" => "Unpaid");
        Hooks::run_hook("InvoiceCreation", $invoiceArr);
        $result2 = \App\Models\Paymentgateway::select('value')->where(array("gateway" => $invpaymentmethod, "setting" => "type"));
        $data2 = $result2;
        $paymenttype = $data2->value("value") ?? "";
        if ($noemails != "true") {
            Hooks::run_hook("InvoiceCreationPreEmail", $invoiceArr);
            \App\Helpers\Functions::sendMessage(($paymenttype == "CC" || $paymenttype == "OfflineCC" ? "Credit Card " : "") . "Invoice Created", $invoiceid);
        }
        Hooks::run_hook("InvoiceCreated", $invoiceArr);
        $total = $invoice->total;
        if ($total == "0.00") {
            $doprocesspaid = true;
        }
        session()->put("InOrderButNeedProcessPaidInvoiceAction", false);
        if ($doprocesspaid) {
            if (defined("INORDERFORM")) {
                session()->put("InOrderButNeedProcessPaidInvoiceAction", true);
            } else {
                \App\Helpers\Invoice::processPaidInvoice($invoiceid);
            }
        }
        $invoicetotal = 0;
        $invoicecount++;
        \App\Helpers\Invoice::adjustIncrementForNextInvoice($invoiceid);
    }
}

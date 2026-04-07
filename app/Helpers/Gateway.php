<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use App\Helpers\Service;
use App\Helpers\LogActivity;
use App\Helpers\Hooks;
use App\Helpers\Cfg;
use App\Models\Hosting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Gateway
{
    protected $request;
    
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public static function CheckActiveGateway()
    {
        return count(self::GetGatewaysArray()) > 0;
    }

    public static function GetGatewaysArray()
    {
        $data = [];
        $gatewayInterface = new \App\Module\Gateway();
        $ActiveGateways = $gatewayInterface->getActiveGateways();
        $ActiveGatewaysData = array_map(function ($gateway) {
            return $gateway->getLowerName();
        }, $ActiveGateways);

        $result3 = \App\Models\Paymentgateway::where('setting', 'name')
            ->whereIn('gateway', $ActiveGatewaysData)
            ->orderBy('order', 'ASC')
            ->get()
            ->transform(function ($pg) {
                $pg->gateway = strtolower($pg->gateway);
                return $pg;
            })
            ->toArray();

        foreach ($result3 as $gateway) {
            $data[$gateway['gateway']] = $gateway['value'];
        }

        return $data;
    }

    public static function CancelSubscriptionForService($serviceID, $userID = 0)
    {
        $userID = (int) $userID;
        $serviceID = (int) $serviceID;

        if ($serviceID == 0) {
            throw new \InvalidArgumentException("Required value serviceID Missing");
        }

        $serviceData = new Service($serviceID, $userID == 0 ? "" : $userID);

        if ($userID == 0) {
            $userID = $serviceData->getData("userid");
        }

        $subscriptionID = $serviceData->getData("subscriptionid");
        if (!$subscriptionID) {
            throw new \InvalidArgumentException("Required value SubscriptionID Missing");
        }
        $paymentMethod = $serviceData->getData("paymentmethod");
        $gateway = $paymentMethod;
        $module = \Module::find($gateway);

        if ($module) {
            $params = ["subscriptionID" => $subscriptionID];
            $className = "\\Modules\\Gateways\\{$gateway}\\Http\\Controllers\\{$gateway}Controller";
            $object = new $className();

            try {
                $cancelResult = $object->cancelSubscription($params);
                if (is_array($cancelResult) && $cancelResult["status"] == "success") {
                    Hosting::where('id', $serviceID)->where('userid', $userID)->update(["subscriptionid" => ""]);
                    LogActivity::Save("Subscription Cancellation for ID " . $subscriptionID . " Successful - Service ID: " . $serviceID, $userID);
                    self::logTransaction($paymentMethod, $cancelResult["rawdata"], "Subscription Cancellation Success");
                    return true;
                }
                LogActivity::Save("Subscription Cancellation for ID " . $subscriptionID . " Failed - Service ID: " . $serviceID, $userID);
                self::logTransaction($paymentMethod, $cancelResult["rawdata"], "Subscription Cancellation Failed");
                throw new \App\Exceptions\Gateways\SubscriptionCancellationFailed("Subscription Cancellation Failed");
            } catch (\Exception $e) {
                throw new \App\Exceptions\Gateways\SubscriptionCancellationNotSupported("Subscription Cancellation not Support by Gateway");
            }
        } else {
            throw new \App\Exceptions\Module\NotServicable("Module not in serviceable");
        }
    }

    public static function showPaymentGatewaysList($disabledGateways = [], $userId = null, $forceAll = false)
    {
        $result = \App\Models\Paymentgateway::where('setting', 'name')->orderBy('order', 'ASC')->get();
        $gatewayList = [];
        $allowChoice = (bool) \App\Helpers\Cfg::get("AllowCustomerChangeInvoiceGateway") || $forceAll;
        $clientGateway = self::getClientsPaymentMethod($userId);

        foreach ($result->toArray() as $data) {
            $showPaymentGateway = $data["gateway"];
            if (!$allowChoice && strcasecmp($showPaymentGateway, $clientGateway) !== 0) {
                continue;
            }
            $showPaymentGWValue = $data["value"];
            $module = \Module::find($showPaymentGateway);
            if ($module && $module->isEnabled()) {
                $p = \App\Models\Paymentgateway::where('setting', 'type')->where('gateway', $showPaymentGateway)->first();
                $p1 = \App\Models\Paymentgateway::where('setting', 'visible')->where('gateway', $showPaymentGateway)->first();
                $gatewayType = $p ? $p->value : '';
                $isVisible = (bool) ($p1 ? $p1->value : false);
                if ($isVisible && !in_array($showPaymentGateway, $disabledGateways)) {
                    $gatewayList[$showPaymentGateway] = ["sysname" => $showPaymentGateway, "name" => $showPaymentGWValue, "type" => $gatewayType];
                }
            }
        }

        return $gatewayList;
    }

    public static function getClientsPaymentMethod($userid)
    {
        $gatewayclass = new \App\Module\Gateway();
        $paymentmethod = "";

        if ($userid) {
            $c = \App\Models\Client::find($userid);
            $clientPaymentMethod = $c ? strtolower($c->defaultgateway) : '';
            if ($clientPaymentMethod && $gatewayclass->isActiveGateway($clientPaymentMethod)) {
                $paymentmethod = $clientPaymentMethod;
            }
            if (!$paymentmethod) {
                $i = \App\Models\Invoice::where('userid', $userid)->orderBy('id', 'DESC')->first();
                $invoicePaymentMethod = $i ? strtolower($i->paymentmethod) : '';
                if ($invoicePaymentMethod && $gatewayclass->isActiveGateway($invoicePaymentMethod)) {
                    $paymentmethod = $invoicePaymentMethod;
                }
            }
        }

        if (!$paymentmethod) {
            $paymentmethod = $gatewayclass->getFirstAvailableGateway();
        }

        return $paymentmethod;
    }

    public static function logTransaction($gateway, $data, $result, array $passedParams = [], \App\Module\Gateway $gatewayModule = null)
    {
        global $params;
        if (!$params) {
            $params = [];
        }
        $historyId = 0;
        if (array_key_exists("history_id", $passedParams)) {
            $historyId = $passedParams["history_id"];
            unset($passedParams["history_id"]);
        }
        $params = array_merge($params, $passedParams);
        $invoiceData = "";
        if (isset($params["invoiceid"]) && $params["invoiceid"]) {
            $invoiceData .= "Invoice ID => " . $params["invoiceid"] . "\n";
        }
        if (isset($params["clientdetails"]) && isset($params["clientdetails"]["userid"]) && $params["clientdetails"]["userid"]) {
            $invoiceData .= "User ID => " . $params["clientdetails"]["userid"] . "\n";
        }
        if (isset($params["amount"]) && $params["amount"]) {
            $invoiceData .= "Amount => " . $params["amount"] . "\n";
        }
        $logData = is_array($data) ? self::outputDataArrayToString($data) : $data;
        static $gatewayNames = [];
        if (!array_key_exists($gateway, $gatewayNames)) {
            $gatewayNames[$gateway] = $gateway;
            if (!$gatewayModule) {
                $gatewayModule = new \App\Module\Gateway();
                $loaded = $gatewayModule->load($gateway);
            } else {
                $loaded = $gatewayModule->getLoadedModule() != "";
            }
            if ($loaded) {
                $gatewayConfig = $gatewayModule->getConfiguration();
                if (array_key_exists("FriendlyName", $gatewayConfig)) {
                    $gatewayNames[$gateway] = $gatewayConfig["FriendlyName"]["Value"];
                }
            }
        }
        $gateway = $gatewayNames[$gateway];
        $array = [
            "date" => \Carbon\Carbon::now(),
            "gateway" => $gateway,
            "data" => $invoiceData . $logData,
            "result" => $result,
            "transaction_history_id" => $historyId
        ];
        \App\Models\Gatewaylog::insert($array);
        Hooks::run_hook("LogTransaction", $array);
    }

    public static function outputDataArrayToString(array $data, $depth = 0)
    {
        $logData = "";
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $logData .= str_repeat("    ", $depth) . (string) $key . " => \n";
                $logData .= self::outputDataArrayToString($value, $depth + 1);
            } else {
                $logData .= str_repeat("    ", $depth) . (string) $key . " => " . $value . "\n";
            }
        }
        return $logData;
    }

    public function paymentMethodsSelection($blankSelection = "", $tabIndex = false, $initWithSelectTag = false)
    {
        global $paymentmethod;

        $tabIndexAttr = $tabIndex ? " tabindex=\"" . $tabIndex . "\"" : "";
        $code = $initWithSelectTag ? "<select name=\"paymentmethod\" class=\"form-control select-inline\"" . $tabIndexAttr . ">" : "";

        if ($blankSelection) {
            $code .= "<option value=\"\">" . $blankSelection . "</option>";
        }

        $result = \App\Models\Paymentgateway::select("gateway", "value")
            ->where("setting", "name")
            ->orderBy("order", "ASC")
            ->get()
            ->toArray();

        foreach ($result as $data) {
            $gateway = $data["gateway"];
            $value = $data["value"];
            $selected = $paymentmethod == $gateway ? " selected" : "";
            $code .= "<option value=\"" . $gateway . "\"" . $selected . ">" . $value . "</option>";
        }

        if ($initWithSelectTag) {
            $code .= "</select>";
        }

        return $code;
    }

    /**
     * paymentMethodsList
     */
    public function paymentMethodsList($blankSelection = "", $tabIndex = false)
    {
        return \App\Models\Paymentgateway::select("gateway", "value")
            ->where("setting", "name")
            ->orderBy("order", "ASC")
            ->get();
    }

    public static function getGatewayVariables($gateway, $invoiceId = "")
    {
        $invoice = new \App\Helpers\InvoiceClass($invoiceId);
        try {
            $params = $invoice->initialiseGatewayAndParams($gateway);
        } catch (\App\Exceptions\Module\NotActivated $e) {
            \App\Helpers\LogActivity::Save("Failed to initialise payment gateway module: " . $e->getMessage());
            throw new \App\Exceptions\Fatal("Gateway Module \"" . \App\Helpers\Sanitize::makeSafeForOutput($gateway) . "\" Not Activated");
        } catch (\Exception $e) {
            \App\Helpers\LogActivity::Save("Failed to initialise payment gateway module: " . $e->getMessage());
            throw new \App\Exceptions\Fatal("Could not initialise payment gateway.");
        }
        if ($invoiceId) {
            $params = array_merge($params, $invoice->getGatewayInvoiceParams());
        }
        return \App\Helpers\Sanitize::convertToCompatHtml($params);
    }

    public static function getRecurringBillingValues($invoiceId)
    {
        global $CONFIG;
        $firstCyclePeriod = "";
        $firstCycleUnits = "";
        $invoiceId = (int) $invoiceId;

        $result = \App\Models\Invoiceitem::selectRaw("tblinvoiceitems.relid, tblhosting.userid, tblhosting.billingcycle, tblhosting.packageid, tblhosting.regdate, tblhosting.nextduedate")
            ->where(["invoiceid" => $invoiceId, "type" => "Hosting"])
            ->orderBy("tblinvoiceitems.id", "ASC")
            ->join("tblhosting", "tblhosting.id", "=", "tblinvoiceitems.relid");

        $data = $result;
        $relatedId = $data->value("relid");
        $userId = $data->value("userid");
        $billingCycle = $data->value("billingcycle");
        $packageId = $data->value("packageid");
        $registrationDate = $data->value("regdate");
        $nextDueDate = $data->value("nextduedate");

        if (!$relatedId || $billingCycle == "One Time" || $billingCycle == "Free Account") {
            return false;
        }

        $result = \App\Models\Invoice::selectRaw("total, taxrate, taxrate2, paymentmethod, (SELECT SUM(amountin)-SUM(amountout) FROM tblaccounts WHERE invoiceid=tblinvoices.id) AS amountpaid")
            ->where(["id" => $invoiceId]);

        $data = $result;
        $total = $data->value("total");
        $taxRate = $data->value("taxrate");
        $taxRate2 = $data->value("taxrate2");
        $paymentMethod = $data->value("paymentmethod");
        $amountPaid = $data->value("amountpaid");
        $firstPaymentAmount = $total - $amountPaid;
        $recurringCyclePeriod = \App\Helpers\Invoice::getBillingCycleMonths($billingCycle);
        $recurringCycleUnits = "Months";

        if ($recurringCyclePeriod >= 12) {
            $recurringCyclePeriod /= 12;
            $recurringCycleUnits = "Years";
        }

        $taxCalculator = new \App\Helpers\Tax();
        $taxCalculator->setIsInclusive($CONFIG["TaxType"] == "Inclusive")
            ->setIsCompound($CONFIG["TaxL2Compound"])
            ->setLevel1Percentage($taxRate)
            ->setLevel2Percentage($taxRate2);

        $recurringAmount = 0;
        $result = \App\Models\Invoiceitem::selectRaw("tblhosting.amount, tblinvoiceitems.amount as invoiced_amount, tblinvoiceitems.taxed")
            ->join("tblhosting", "tblhosting.id", "=", "tblinvoiceitems.relid")
            ->where(["tblinvoiceitems.invoiceid" => $invoiceId, "tblinvoiceitems.type" => "Hosting", "tblhosting.billingcycle" => $billingCycle])
            ->get();

        $recurringTax = [];
        foreach ($result->toArray() as $data) {
            $productAmount = $data["amount"];
            $invoicedAmount = $data["invoiced_amount"];
            $taxed = $data["taxed"];
            if ($taxed) {
                if ($invoicedAmount <= $productAmount) {
                    $recurringTax[] = $productAmount;
                } else {
                    $recurringTax[] = $invoicedAmount;
                    $recurringTax[] = $productAmount - $invoicedAmount;
                }
            }
            $recurringAmount += $productAmount;
        }

        $productTax1 = $productTax2 = 0;
        if (Cfg::getValue("TaxPerLineItem")) {
            foreach ($recurringTax as $taxBase) {
                $taxCalculator->setTaxBase($taxBase);
                $productTax1 += $taxCalculator->getLevel1TaxTotal();
                $productTax2 += $taxCalculator->getLevel2TaxTotal();
            }
        } else {
            $taxCalculator->setTaxBase(array_sum($recurringTax));
            $productTax1 = $taxCalculator->getLevel1TaxTotal();
            $productTax2 = $taxCalculator->getLevel2TaxTotal();
        }

        if ($CONFIG["TaxType"] == "Exclusive") {
            $recurringAmount += $productTax1 + $productTax2;
        }

        $result = \App\Models\Invoiceitem::selectRaw("tblhostingaddons.recurring, tblhostingaddons.tax")
            ->join("tblhostingaddons", "tblhostingaddons.id", "=", "tblinvoiceitems.relid")
            ->where(["tblinvoiceitems.invoiceid" => $invoiceId, "tblinvoiceitems.type" => "Addon", "tblhostingaddons.billingcycle" => $billingCycle])
            ->get();

        foreach ($result->toArray() as $data) {
            list($addonAmount, $addonTax) = $data;
            if ($CONFIG["TaxType"] == "Exclusive" && $addonTax) {
                if ($CONFIG["TaxL2Compound"]) {
                    $addonAmount += $addonAmount * $taxRate / 100;
                    $addonAmount += $addonAmount * $taxRate2 / 100;
                } else {
                    $addonAmount += \App\Helpers\Functions::format_as_currency($addonAmount * $taxRate / 100) + \App\Helpers\Functions::format_as_currency($addonAmount * $taxRate2 / 100);
                }
            }
            $recurringAmount += $addonAmount;
        }

        if (in_array($billingCycle, ["Annually", "Biennially", "Triennially"])) {
            $cycleregperiods = ["Annually" => "1", "Biennially" => "2", "Triennially" => "3"];
            $result = \App\Models\Invoiceitem::selectRaw("SUM(tbldomains.recurringamount) as total")
                ->join("tbldomains", "tbldomains.id", "=", "tblinvoiceitems.relid")
                ->where("tblinvoiceitems.invoiceid", $invoiceId)
                ->whereIn("tblinvoiceitems.type", ['DomainRegister', 'DomainTransfer', 'Domain'])
                ->where("tbldomains.registrationperiod", $cycleregperiods[$billingCycle]);

            $data = $result;
            $domainAmount = $data->value('total');
            if ($CONFIG["TaxType"] == "Exclusive" && $CONFIG["TaxDomains"]) {
                if ($CONFIG["TaxL2Compound"]) {
                    $domainAmount += $domainAmount * $taxRate / 100;
                    $domainAmount += $domainAmount * $taxRate2 / 100;
                } else {
                    $domainAmount += \App\Helpers\Functions::format_as_currency($domainAmount * $taxRate / 100) + \App\Helpers\Functions::format_as_currency($domainAmount * $taxRate2 / 100);
                }
            }
            $recurringAmount += $domainAmount;
        }

        $result = \App\Models\Invoice::where('id', $invoiceId);
        $data = $result;
        $invoiceDueDate = $data->value("duedate");
        $invoiceDueDate = str_replace("-", "", $invoiceDueDate);
        $overdue = $invoiceDueDate < date("Ymd");

        $result = \App\Models\Product::where('id', $packageId);
        $data = $result;
        $proRataBilling = $data->value("proratabilling");
        $proRataDate = $data->value("proratadate");
        $proRataChargeNextMonth = $data->value("proratachargenextmonth");

        if ($registrationDate == $nextDueDate && $proRataBilling) {
            $orderYear = substr($registrationDate, 0, 4);
            $orderMonth = substr($registrationDate, 5, 2);
            $orderDay = substr($registrationDate, 8, 2);
            $proRataValues = \App\Helpers\Invoice::getProrataValues($billingCycle, 0, $proRataDate, $proRataChargeNextMonth, $orderDay, $orderMonth, $orderYear, $userId);
            $firstCyclePeriod = $proRataValues["days"];
            $firstCycleUnits = "Days";
        }

        if (!$firstCyclePeriod) {
            $firstCyclePeriod = $recurringCyclePeriod;
        }
        if (!$firstCycleUnits) {
            $firstCycleUnits = $recurringCycleUnits;
        }

        $result = \App\Models\Paymentgateway::where(["gateway" => $paymentMethod, "setting" => "convertto"]);
        $data = $result;
        $convertTo = $data->value("value");

        if ($convertTo) {
            $currency = \App\Helpers\Format::getCurrency($userId);
            $firstPaymentAmount = \App\Helpers\Invoice::convertCurrency($firstPaymentAmount, $currency["id"], $convertTo);
            $recurringAmount = \App\Helpers\Invoice::convertCurrency($recurringAmount, $currency["id"], $convertTo);
        }

        $firstPaymentAmount = \App\Helpers\Functions::format_as_currency($firstPaymentAmount);
        $recurringAmount = \App\Helpers\Functions::format_as_currency($recurringAmount);

        $recurringBillingValues = [
            "primaryserviceid" => $relatedId,
            "recurringamount" => $recurringAmount,
            "recurringcycleperiod" => $recurringCyclePeriod,
            "recurringcycleunits" => $recurringCycleUnits,
            "overdue" => $overdue
        ];

        if ($firstPaymentAmount != $recurringAmount) {
            $recurringBillingValues["firstpaymentamount"] = $firstPaymentAmount;
            $recurringBillingValues["firstcycleperiod"] = $firstCyclePeriod;
            $recurringBillingValues["firstcycleunits"] = $firstCycleUnits;
        }

        return $recurringBillingValues;
    }

    public static function getUpgradeRecurringValues($invoiceID)
    {
        global $CONFIG;
        $invoiceID = (int) $invoiceID;

        if ($invoiceID == 0) {
            throw new \InvalidArgumentException("Required value InvoiceID Missing");
        }

        $data = DB::table("tblinvoiceitems")
            ->join("tblupgrades", "tblupgrades.id", "=", "tblinvoiceitems.relid")
            ->where("invoiceid", $invoiceID)
            ->where("tblinvoiceitems.type", "Upgrade")
            ->orderBy("tblinvoiceitems.id", "ASC")
            ->first(["tblinvoiceitems.relid", "tblinvoiceitems.taxed", "tblinvoiceitems.userid", "tblupgrades.relid as service", "tblupgrades.originalvalue", "tblupgrades.newvalue", "tblupgrades.orderid", "tblupgrades.type"]);

        if (is_null($data)) {
            return false;
        }

        $relID = $data->service;
        $taxed = $data->taxed;
        $userID = $data->userid;

        if ($data->type == "package") {
            $packageData = explode(",", $data->newvalue);
            list($packageID, $billingCycle) = $packageData;
        } else {
            $packageData = new \App\Helpers\Service($relID);
            $packageID = $packageData->getData("packageid");
            $billingCycle = $packageData->getData("billingcycle");
        }

        $promoID = 0;
        $order = new \App\Helpers\OrderClass();
        $order->setID($data->orderid);
        $promoCode = $order->getData("promocode");

        if ($promoCode) {
            $promoID = DB::table("tblpromotions")->where("code", "=", $promoCode)->value("id");
        }

        if (!$relID || $billingCycle == "onetime" || $billingCycle == "free") {
            throw new \InvalidArgumentException("Not Recurring or Missing ServiceID");
        }

        $cycle = $billingCycle == "semiannually" ? "Semi-Annually" : ucfirst($billingCycle);
        $recurringAmount = \App\Helpers\ProcessInvoices::recalcRecurringProductPrice($relID, $userID, $packageID, $cycle, "empty", $promoID);

        $invoice = new \App\Helpers\InvoiceClass($invoiceID);
        $total = $invoice->getData("total");
        $taxRate = $invoice->getData("taxrate");
        $taxRate2 = $invoice->getData("taxrate2");
        $amountPaid = $invoice->getData("amountpaid");
        $firstPaymentAmount = $total - $amountPaid;
        $recurringCyclePeriod = \App\Helpers\Invoice::getBillingCycleMonths($billingCycle);
        $recurringCycleUnits = "Months";

        if ($recurringCyclePeriod >= 12) {
            $recurringCyclePeriod /= 12;
            $recurringCycleUnits = "Years";
        }

        if ($CONFIG["TaxType"] == "Exclusive" && $taxed) {
            if ($CONFIG["TaxL2Compound"]) {
                $recurringAmount += $recurringAmount * $taxRate / 100;
                $recurringAmount += $recurringAmount * $taxRate2 / 100;
            } else {
                $recurringAmount += $recurringAmount * $taxRate / 100 + $recurringAmount * $taxRate2 / 100;
            }
        }

        $recurringAmount = \App\Helpers\Functions::format_as_currency($recurringAmount);
        $invoiceDueDate = $invoice->getData("duedate");
        $invoiceDueDate = str_replace("-", "", $invoiceDueDate);
        $overdue = $invoiceDueDate < date("Ymd");

        $service = new \App\Helpers\Service($relID);
        $dateUntil = $service->getData("nextduedate");

        if ($dateUntil == "0000-00-00") {
            $dateUntil = \App\Helpers\Invoice::getInvoicePayUntilDate($invoice->getData("duedate"), $billingCycle);
        }

        $currentServicePaidUntil = \App\Helpers\Carbon::createFromFormat("Y-m-d", $dateUntil);
        $newServiceStartDate = \App\Helpers\Carbon::createFromFormat("Y-m-d H:i:s", $invoice->getData("duedate"));

        if ($newServiceStartDate < $currentServicePaidUntil) {
            $days = $currentServicePaidUntil->diffInDays($newServiceStartDate);
            $returnData = [
                "primaryserviceid" => $relID,
                "recurringamount" => $recurringAmount,
                "recurringcycleperiod" => $recurringCyclePeriod,
                "recurringcycleunits" => $recurringCycleUnits,
                "overdue" => $overdue
            ];

            if ($firstPaymentAmount != $recurringAmount) {
                $returnData["firstpaymentamount"] = $firstPaymentAmount;
                $returnData["firstcycleperiod"] = $days;
                $returnData["firstcycleunits"] = "Days";
            }

            return $returnData;
        }

        $message = "Delinquent service cannot be upgraded. Service ID: " . $service->getID() . ", upgrade invoice ID: " . $invoice->getID();
        throw new \InvalidArgumentException($message);
    }

    public static function checkCbInvoiceID($invoiceId, $gateway = "Unknown")
    {
        $result = \App\Models\Invoice::where(["id" => $invoiceId]);
        $data = $result;
        $id = $data->value("id");

        if (!$id) {
            self::logTransaction($gateway, request()->all(), "Invoice ID Not Found");
            return '';
        }

        return $id;
    }

    public static function checkCbTransID($transactionId)
    {
        $result = \App\Models\Account::where(["transid" => $transactionId]);
        $numRows = $result->count();

        if ($numRows) {
            return '';
        }
    }

    public static function invoiceSaveRemoteCard($invoiceId, $cardNumberOrLastFour, $cardType, $expiryDate, $remoteToken)
    {
        DB::beginTransaction();
        try {
            \App\Models\Invoice::findOrFail($invoiceId)->saveRemoteCard($cardNumberOrLastFour, $cardType, $expiryDate, $remoteToken);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            Log::debug("invoiceSaveRemoteCard ERROR: " . $e->getMessage());
            DB::rollback();
            return false;
        }
    }

    public static function callback3DSecureRedirect($invoiceId, $success = false)
    {
        global $CONFIG;
        $redirectPage = route('pages.services.mydomains.viewinvoiceweb', $invoiceId) . "?";
        $redirectPage .= $success ? "paymentsuccess=true" : "paymentfailed=true";

        return "<html>
            <head>
                <title>" . $CONFIG["CompanyName"] . "</title>
            </head>
            <body onload=\"document.frmResultPage.submit();\">
                <form name=\"frmResultPage\" method=\"post\" action=\"" . $redirectPage . "\" target=\"_parent\">
                    <noscript>
                        <br>
                        <br>
                        <center>
                            <p style=\"color:#cc0000;\"><b>Processing Your Transaction</b></p>
                            <p>JavaScript is currently disabled or is not supported by your browser.</p>
                            <p>Please click Submit to continue the processing of your transaction.</p>
                            <input type=\"submit\" value=\"Submit\">
                        </center>
                    </noscript>
                </form>
            </body>
        </html>";
    }
}
<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here
use DB;
use App\Helpers\LogActivity;
use App\Helpers\Cfg;
use App\Helpers\Hooks;

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class OrderClass
{
	private $orderId = 0;
    private $data = array();
    public function setID($orderId)
    {
        $this->orderId = (int) $orderId;
        $this->loadData();
        return $this;
    }
	protected function loadData()
    {
        try {
            $orderData = \App\Models\Order::findOrFail($this->orderId);
            $this->data = $orderData->toArray();
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }
	public function getData($key)
    {
        if (!$this->data) {
            $this->loadData();
        }
        $keyParts = explode(".", $key);
        if (count($keyParts) == 1) {
            return isset($this->data[$key]) ? $this->data[$key] : "";
        }
        $value = $this->data;
        foreach ($keyParts as $key) {
            $value = isset($value[$key]) ? $value[$key] : "";
        }
        return $value;
    }
	public function getActiveFraudModule()
    {
        $fraudModule = DB::table("tblfraud")->where("setting", "Enable")->where("value", "on")->first();
        $module = "";
        if ($fraudModule) {
            $module = $fraudModule->fraud;
        }
        return $module;
    }
	public function shouldFraudCheckBeSkipped()
    {
        $fraudModule = "";
        $userId = (int) $this->getData("userid");
        try {
            $this->skipFraudCheckBecausePaidByCredit();
            $this->skipFraudCheckBecauseOfExistingOrders();
            $this->shouldFraudCheckBeSkippedByHook();
        } catch (\App\Exceptions\Order\SkipFraudCheck $e) {
            LogActivity::Save("Order ID " . $this->orderId . " Skipped Fraud Check due to Already Active Orders", $userId);
            $fraudModule = "SKIPPED";
        } catch (\App\Exceptions\Order\HookSkipFraudCheck $e) {
            LogActivity::Save("Order ID " . $this->orderId . " Skipped Fraud Check due to Custom Hook", $userId);
            $fraudModule = "SKIPPED";
        } catch (\App\Exceptions\Order\PaidByCredit $e) {
            $fraudModule = "CREDIT";
        }
        if ($fraudModule) {
            DB::table("tblorders")->where("id", $this->orderId)->update(array("fraudmodule" => $fraudModule, "fraudoutput" => ""));
            return true;
        }
        return false;
    }
	protected function skipFraudCheckBecauseOfExistingOrders()
    {
        if (Cfg::get("SkipFraudForExisting")) {
            $userId = (int) $this->getData("userid");
            $existingOrderCount = \App\Models\Order::where("status", "Active")->where("userid", $userId)->count();
            if ($existingOrderCount) {
                throw new \App\Exceptions\Order\SkipFraudCheck("Existing Order Found");
            }
        }
    }
	protected function shouldFraudCheckBeSkippedByHook()
    {
        $userId = $this->getData("userid");
        $hookParams = array("orderid" => $this->orderId, "userid" => $userId);
        $hookResponses = Hooks::run_hook("RunFraudCheck", $hookParams);
        foreach ($hookResponses as $hookResponse) {
            if ($hookResponse) {
                throw new \App\Exceptions\Order\HookSkipFraudCheck("Skipped By Hook");
            }
        }
    }
	protected function skipFraudCheckBecausePaidByCredit()
    {
        $paidByCredit = DB::table("tblinvoices")->join("tblorders", "tblorders.invoiceid", "=", "tblinvoices.id")->where("tblorders.id", $this->orderId)->where("tblinvoices.subtotal", "!=", "0")->where("tblinvoices.credit", ">", 0)->where("tblinvoices.total", "=", "0")->count();
        if ($paidByCredit) {
            throw new \App\Exceptions\Order\PaidByCredit("Paid By Credit");
        }
    }
}

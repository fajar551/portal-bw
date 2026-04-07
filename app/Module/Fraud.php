<?php
namespace App\Module;

use DB, Auth;
use App\Helpers\LogActivity;
use App\Helpers\Cfg;
use App\Helpers\Hooks;
use Illuminate\Support\Facades\Request;

class Fraud extends AbstractModule
{
    protected $type = self::TYPE_FRAUD;
    const SKIP_MODULES = array("SKIPPED", "CREDIT");

    public function getActiveModules()
    {
        return \DB::table("tblfraud")->where("setting", "Enable")->where("value", "!=", "")->distinct("fraud")->pluck("fraud");
    }
    public function load($module, $globalVariable = NULL)
    {
        if (in_array($module, self::SKIP_MODULES)) {
            return false;
        }
        return parent::load($module);
    }
    public function getSettings()
    {
        return DB::table("tblfraud")->where("fraud", $this->getLoadedModule())->pluck("value", "setting")->toArray();
    }
    public function call($function, array $params = array())
    {
        $params = array_merge($params, $this->getSettings());
        return parent::call($function, $params);
    }
    public function doFraudCheck($orderid, $userid = "", $ip = "")
    {
        $params = array();
        $params["ip"] = $ip ? $ip : request()->ip();
        $params["forwardedip"] = request()->server("HTTP_X_FORWARDED_FOR");
        $userid = (int) $userid;
        if (!$userid) {
            $auth = Auth::guard('web')->user();
            $userid = $auth ? $auth->id : 0;
        }
        $clientsdetails = \App\Helpers\ClientHelper::getClientsDetails($userid);
        $params["clientsdetails"] = $clientsdetails;
        $params["clientsdetails"]["countrycode"] = $clientsdetails["phonecc"];
        $order = \App\Models\Order::find($orderid);
        $params["orderid"] = $order->id;
        $params["order"] = array("id" => $order->id, "order_number" => $order->orderNumber, "amount" => $order->amount, "payment_method" => $order->paymentMethod, "promo_code" => $order->promoCode);
        if (!defined("ADMINAREA")) {
            $params["sessionId"] = session()->getId();
            $params["userAgent"] = request()->server("HTTP_USER_AGENT");
            $params["acceptLanguage"] = request()->server("HTTP_ACCEPT_LANGUAGE");
        }
        $hookResponses = \App\Helpers\hooks::run_hook("PreFraudCheck", $params);
        foreach ($hookResponses as $hookResponse) {
            $params = array_merge($params, $hookResponse);
        }
        $response = $this->call("doFraudCheck", $params);
        $output = "";
        if ($response) {
            if (version_compare($this->getAPIVersion(), "1.2", ">=")) {
                $responseData = is_array($response["data"]) ? $response["data"] : array();
                $output = json_encode($responseData);
            } else {
                foreach ($response as $key => $value) {
                    if (!in_array($key, array("userinput", "error", "title", "description"))) {
                        $output .= $key . " => " . $value . "\n";
                    }
                }
            }
        }
        $order->fraudModule = $this->getLoadedModule();
        $order->fraudOutput = $output;
        $order->save();
        $response["fraudoutput"] = $output;
        return $response;
    }
    public function processResultsForDisplay($orderid, $fraudoutput = "")
    {
        if ($orderid && !$fraudoutput) {
            $data = \App\Models\Order::where('id', $orderid)->where('fraudmodule', $this->getLoadedModule())->first();
            $data = $data->toArray();
            $fraudoutput = $data["fraudoutput"];
        }
        $results = $this->call("processResultsForDisplay", array("data" => $fraudoutput));
        $fraudResults = \App\Helpers\Sanitize::makeSafeForOutput($results);
        // TODO: if (version_compare($this->getAPIVersion(), "1.2", ">=") && is_string($results)) {
        if (is_string($results)) {
            $return = $results;
        } else {
            $return = "<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\"><tr>";
            $i = 0;
            foreach ($fraudResults as $key => $value) {
                $i++;
                $colspan = "";
                $width = "";
                $end = "";
                if ($key == "Explanation") {
                    $colspan = " colspan=\"3\"";
                    $i = 2;
                } else {
                    $width = " width=\"20%\"";
                }
                if ($i == 2) {
                    $end = "</tr><tr>";
                    $i = 0;
                }
                $return .= "<td class=\"fieldlabel\" width=\"30%\">" . $key . "</td>" . "<td class=\"fieldarea\"" . $colspan . $width . ">" . $value . "</td>" . $end;
            }
            $return .= "</tr></table>";
        }
        return $return;
    }
    public function getAdminActivationForms($moduleName)
    {
        return array((new \App\Helpers\Form())->setUriPrefixAdminBaseUrl("configfraud.php")->setMethod(\App\Helpers\Form::METHOD_GET)->setParameters(array("fraud" => $moduleName))->setSubmitLabel(\Lang::get("admin.globalactivate")));
    }
    public function getAdminManagementForms($moduleName)
    {
        return array((new \App\Helpers\Form())->setUriPrefixAdminBaseUrl("configfraud.php")->setMethod(\App\Helpers\Form::METHOD_GET)->setParameters(array("fraud" => $moduleName))->setSubmitLabel(\Lang::get("admin.globalmanage")));
    }
}

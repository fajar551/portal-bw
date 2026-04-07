<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Helpers\Cfg;
use DB;

class Gateways
{
	private $modulename = "";
    private static $gateways = NULL;
    private $displaynames = array();
    const CC_EXPIRY_MAX_YEARS = 20;
    public function getDisplayNames()
    {
		$result = \App\Models\Paymentgateway::where(array("setting" => "name"))->orderBy("order", "ASC")->get();
        foreach ($result->toArray() as $data) {
            $this->displaynames[$data["gateway"]] = $data["value"];
        }
        return $this->displaynames;
    }
    public function getDisplayName($gateway)
    {
        if (empty($this->displaynames)) {
            $this->getDisplayNames();
        }
        return array_key_exists($gateway, $this->displaynames) ? $this->displaynames[$gateway] : $gateway;
    }
    public static function isNameValid($gateway)
    {
        if (!is_string($gateway) || empty($gateway)) {
            return false;
        }
        if (!ctype_alnum(str_replace(array("_", "-"), "", $gateway))) {
            return false;
        }
        return true;
    }
    public static function getActiveGateways()
    {
        if (is_array(self::$gateways)) {
            return self::$gateways;
        }
        self::$gateways = array();
		$result = \App\Models\Paymentgateway::selectRaw("DISTINCT gateway")->get();
        foreach ($result->toArray() as $data) {
            $gateway = $data['gateway'] ?? "";
            if (Gateways::isNameValid($gateway)) {
                self::$gateways[] = $gateway;
            }
        }
        return self::$gateways;
    }
    public function getAvailableGatewayInstances($onlyStoreRemote = false)
    {
        $modules = array();
        $gatewaysAggregator = new static();
        foreach (array_keys($gatewaysAggregator->getAvailableGateways()) as $name) {
            $module = new \App\Module\Gateway();
            if ($module->isActiveGateway($name) && $module->load($name)) {
                if ($onlyStoreRemote) {
                    if ($module->functionExists("storeremote")) {
                        $modules[$name] = $module;
                    }
                } else {
                    $modules[$name] = $module;
                }
            }
        }
        return $modules;
    }
    public function isActiveGateway($gateway)
    {
        $gateways = $this->getActiveGateways();
        return in_array($gateway, $gateways);
    }
    public static function makeSafeName($gateway)
    {
        $validgateways = Gateways::getActiveGateways();
        return in_array($gateway, $validgateways) ? $gateway : "";
    }
    public function getAvailableGateways($invoiceid = "")
    {
        $validgateways = array();

		$result = DB::select(DB::raw("SELECT DISTINCT gateway, (SELECT value FROM tblpaymentgateways g2 WHERE g1.gateway=g2.gateway AND setting='name' LIMIT 1) AS `name`, (SELECT `order` FROM tblpaymentgateways g2 WHERE g1.gateway=g2.gateway AND setting='name' LIMIT 1) AS `order` FROM `tblpaymentgateways` g1 WHERE setting='visible' AND value='on' ORDER BY `order` ASC"));
        $result = array_map(function ($value) {
            return (array)$value;
        }, $result);
		foreach ($result as $key => $data) {
            if (\Module::find($data['gateway'])) {
                $validgateways[$data['gateway']] = $data['name'];
            }
        }
        if ($invoiceid) {
            $invoiceid = (int) $invoiceid;
			$invoicegateway = \App\Models\Invoice::where(array("id" => $invoiceid))->value("paymentmethod") ?? "";
            $result = \App\Models\Invoiceitem::where(array("type" => "Hosting", "invoiceid" => $invoiceid))->get();
            foreach ($result->toArray() as $data) {
                $relid = $data["relid"];
    //             if ($relid) {
				// 	$result2 = DB::select(DB::raw("SELECT pg.disabledgateways AS disabled FROM tblhosting h LEFT JOIN tblproducts p on h.packageid = p.id LEFT JOIN tblproductgroups pg on p.gid = pg.id where h.id = " . (int) $relid));
    //                 // $gateways = explode(",", $result2[0]->disabled);
    //                 $gateways = $result2;
    //                 foreach ($gateways as $gateway) {
    //                     if (array_key_exists($gateway, $validgateways) && $gateway != $invoicegateway) {
    //                         unset($validgateways[$gateway]);
    //                     }
    //                 }
    //             }
            // Periksa apakah $result2 tidak kosong
            if (!empty($result2)) {
                // Ambil nilai disabled gateways dan pecah menjadi array
                $disabledGateways = explode(",", $result2[0]->disabled);
                
                // Loop melalui disabled gateways
                foreach ($disabledGateways as $gateway) {
                    // Trimming untuk menghapus spasi
                    $gateway = trim($gateway);
                    
                    // Pastikan gateway adalah string dan ada di validgateways
                    if ($gateway && array_key_exists($gateway, $validgateways) && $gateway != $invoicegateway) {
                        unset($validgateways[$gateway]);
                    }
                }
            } else {
                // Kondisi ketika tidak ada metode pembayaran yang didapatkan
                // Misalnya, tambahkan log atau berikan metode pembayaran default
                Log::warning("Tidak ada metode pembayaran ditemukan untuk invoice ID: " . $invoiceid);
                
                // Opsional: Tambahkan metode pembayaran default
                if (empty($validgateways)) {
                    $defaultGateway = $this->getFirstAvailableGateway();
                    if ($defaultGateway) {
                        $validgateways[$defaultGateway] = $this->getDisplayName($defaultGateway);
                    }
                }
            }
            
            }
            // if (array_key_exists($invoicegateway, $validgateways) === false) {
            //     $validgateways[$invoicegateway] = \App\Models\Paymentgateway::where(array("setting" => "name", "gateway" => $invoicegateway))->value("value") ?? "";
            // }
            if ($invoicegateway && !array_key_exists($invoicegateway, $validgateways)) {
                $validgateways[$invoicegateway] = \App\Models\Paymentgateway::where(array("setting" => "name", "gateway" => $invoicegateway))->value("value") ?? "";
            }
        }
        return $validgateways;
    }
    public function getFirstAvailableGateway()
    {
        $gateways = $this->getAvailableGateways();
        return key($gateways);
    }
    public function getCCDateMonths()
    {
        $months = array();
        for ($i = 1; $i <= 12; $i++) {
            $months[] = str_pad($i, 2, "0", STR_PAD_LEFT);
        }
        return $months;
    }
    public function getCCStartDateYears()
    {
        $startyears = array();
        for ($i = date("Y") - 12; $i <= date("Y"); $i++) {
            $startyears[] = $i;
        }
        return $startyears;
    }
    public function getCCExpiryDateYears()
    {
        $expiryyears = array();
        for ($i = date("Y"); $i <= date("Y") + static::CC_EXPIRY_MAX_YEARS; $i++) {
            $expiryyears[] = $i;
        }
        return $expiryyears;
    }
    public function getActiveMerchantGatewaysByType()
    {
        $groupedGateways = array("assisted" => array(), "merchant" => array(), "remote" => array(), "thirdparty" => array(), "token" => array());
        $query = DB::table("tblpaymentgateways as gw1")->where("gw1.setting", "type")->where("gw1.value", "CC")->leftJoin("tblpaymentgateways as gw2", "gw1.gateway", "=", "gw2.gateway")->where("gw2.setting", "visible");
        $gateways = $query->get(array("gw1.gateway", "gw2.value as visible"));
        foreach ($gateways as $gatewayData) {
            $gateway = $gatewayData->gateway;
            $gatewayInterface = new \App\Module\Gateway();
            $gatewayInterface->load($gateway);
            $groupedGateways[$gatewayInterface->getWorkflowType()][$gateway] = (bool) $gatewayData->visible;
        }
        return $groupedGateways;
    }
    public function isLocalCreditCardStorageEnabled($client = true)
    {
        $merchantGateways = $this->getActiveMerchantGatewaysByType()[\App\Module\Gateway::WORKFLOW_MERCHANT];
        if ($client) {
            $merchantGateways = array_filter($merchantGateways);
        }
        return 0 < count($merchantGateways);
    }
    public function isIssueDateAndStartNumberEnabled()
    {
        return (bool) Cfg::getValue("ShowCCIssueStart");
    }
    public function isLocalBankAccountGatewayAvailable()
    {
        foreach ($this->getAvailableGatewayInstances() as $gatewayInstance) {
            if ($gatewayInstance->supportsLocalBankDetails()) {
                return true;
            }
        }
        return false;
    }
}

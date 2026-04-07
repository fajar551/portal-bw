<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use DB;

class DomainsHelper
{
	public function enable()
    {
        \App\Helpers\Cfg::setValue("AllowRegister", "on");
        \App\Helpers\Cfg::setValue("AllowTransfer", "on");
        \App\Helpers\Cfg::setValue("AllowOwnDomain", "on");
    }
    public function disable()
    {
        \App\Helpers\Cfg::setValue("AllowRegister", "");
        \App\Helpers\Cfg::setValue("AllowTransfer", "");
        \App\Helpers\Cfg::setValue("AllowOwnDomain", "");
    }
    public function setupTldsWithDefaultOptions($extensions, $registrar, $price)
    {
        if (!is_array($extensions) || count($extensions) == 0) {
            return NULL;
        }
        if (!is_numeric($price)) {
            throw new \Exception("A selling price is required.");
        }
        if ($price <= 0) {
            throw new \Exception("Selling price must be greater than 0.");
        }
        foreach ($extensions as $extension) {
            try {
                $this->addTld($extension, false, false, false, true, $registrar, $price);
            } catch (\Exception $e) {
            }
        }
    }
    public function addTld($extension, $dnsManagement = false, $emailForwarding = false, $idProtection = false, $requiresEppCode = false, $registrar = "", $price = -1, $tldGroup = "")
    {
        if (substr($extension, 0, 1) != ".") {
            $extension = "." . $extension;
        }
        $tld = DB::table("tbldomainpricing")->where("extension", "=", $extension)->get();
        if (0 < count($tld)) {
            throw new \Exception("Extension already exists.");
        }
        $lastOrder = DB::table("tbldomainpricing")->orderBy("order", "desc")->first();
        if (is_null($lastOrder)) {
            $lastOrder = 0;
        } else {
            $lastOrder = $lastOrder->order;
        }
        if ($tldGroup && !in_array($tldGroup, array("sale", "new", "hot"))) {
            $tldGroup = "";
        }
        $extensionId = DB::table("tbldomainpricing")->insertGetId(array("extension" => $extension, "dnsmanagement" => (int) $dnsManagement, "emailforwarding" => (int) $emailForwarding, "idprotection" => (int) $idProtection, "eppcode" => (int) $requiresEppCode, "autoreg" => $registrar, "group" => $tldGroup, "order" => $lastOrder + 1));
        foreach (array("register", "transfer", "renew") as $type) {
            DB::table("tblpricing")->insert(array("type" => "domain" . $type, "currency" => "1", "relid" => $extensionId, "msetupfee" => $price, "qsetupfee" => $type == "register" ? "-1" : "0", "ssetupfee" => $type == "register" ? "-1" : "0", "asetupfee" => $type == "register" ? "-1" : "0", "bsetupfee" => $type == "register" ? "-1" : "0", "monthly" => $type == "register" ? "-1" : "0", "quarterly" => $type == "register" ? "-1" : "0", "semiannually" => $type == "register" ? "-1" : "0", "annually" => $type == "register" ? "-1" : "0", "biennially" => $type == "register" ? "-1" : "0"));
        }
        \App\Helpers\AdminFunctions::logAdminActivity("Domain Pricing TLD Created: '" . $extension . "'");
    }
}

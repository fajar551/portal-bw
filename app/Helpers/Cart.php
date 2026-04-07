<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Cart
{
	public static function bundlesValidateCheckout()
    {
        global $_LANG;
        $sessionCart = \Session::get('cart');
        if (!isset($sessionCart["bundle"])) {
            return "";
        }
        $bundlesess = $sessionCart["bundle"];
        foreach ($bundlesess as $k => $v) {
            unset($bundlesess[$k]["warnings"]);
        }
        $bundledata = $warnings = array();
        foreach ($bundlesess as $bnum => $vals) {
            $bid = $vals["bid"];
            $data = \App\Models\Bundle::find($bid)->toArray();
            $allowpromo = $data["allowpromo"];
            $itemdata = $data["itemdata"];
            $itemdata = (new \App\Helpers\Client())->safe_unserialize($itemdata);
            $bundledata[$bid] = $itemdata;
            if ($sessionCart["promo"] && !$allowpromo) {
                $warnings[] = $_LANG["bundlewarningpromo"];
                $bundlesess[$bnum]["warnings"] = 1;
            }
        }
        $numitemsperbundle = $productbundleddomains = $domainsincart = array();
        foreach ($sessionCart["domains"] as $k => $values) {
            $domainsincart[$values["domain"]] = $k;
        }
        foreach ($sessionCart["products"] as $k => $v) {
            if (isset($v["bnum"])) {
                $bnum = $v["bnum"];
                $bitem = $v["bitem"];
                $pid = $v["pid"];
                $domain = $v["domain"];
                $billingcycle = $v["billingcycle"];
                $configoptions = $v["configoptions"];
                $addons = $v["addons"];
                $bid = $sessionCart["bundle"][$bnum]["bid"];
                $itemdata = $bundledata[$bid][$bitem];
                if ($itemdata["type"] != "product" || $pid != $itemdata["pid"]) {
                    unset($sessionCart["products"][$k]["bnum"]);
                    unset($sessionCart["products"][$k]["bitem"]);
                } else {
                    $numitemsperbundle[$bnum]++;
                    $productname = \App\Models\Product::getProductName($pid);
                    if ($itemdata["billingcycle"] && self::bundlesconvertbillingcycle($itemdata["billingcycle"]) != $billingcycle) {
                        $warnings[] = sprintf($_LANG["bundlewarningproductcycle"], $itemdata["billingcycle"], $productname);
                        $bundlesess[$bnum]["warnings"] = 1;
                    }
                    foreach ($itemdata["configoption"] as $cid => $opid) {
                        if ($opid != $configoptions[$cid]) {
                            $data = \App\Models\Productconfigoption::select("optionname", "optiontype", DB::raw("(SELECT optionname FROM tblproductconfigoptionssub WHERE id=$opid) AS subopname"))->where('id', $cid)->first()->toArray();
                            if ($data["optiontype"] == 1 || $data["optiontype"] == 2) {
                                $warnings[] = sprintf($_LANG["bundlewarningproductconfopreq"], $data["subopname"], $data["optionname"]);
                                $bundlesess[$bnum]["warnings"] = 1;
                            } else {
                                if ($data["optiontype"] == 3) {
                                    if ($opid) {
                                        $warnings[] = sprintf($_LANG["bundlewarningproductconfopyesnoenable"], $data["optionname"]);
                                    } else {
                                        $warnings[] = sprintf($_LANG["bundlewarningproductconfopyesnodisable"], $data["optionname"]);
                                    }
                                    $bundlesess[$bnum]["warnings"] = 1;
                                } else {
                                    if ($data["optiontype"] == 4) {
                                        $warnings[] = sprintf($_LANG["bundlewarningproductconfopqtyreq"], $opid, $data["optionname"]);
                                        $bundlesess[$bnum]["warnings"] = 1;
                                    }
                                }
                            }
                        }
                    }
                    if ($itemdata["addons"]) {
                        foreach ($itemdata["addons"] as $addonid) {
                            if (!in_array($addonid, $addons)) {
                                $a = \App\Models\Addon::find($addonid);
                                $addonName = $a ? $a->name : "";
                                $warnings[] = sprintf($_LANG["bundlewarningproductaddonreq"], $addonName, $productname);
                                $bundlesess[$bnum]["warnings"] = 1;
                            }
                        }
                    }
                    if (array_key_exists($domain, $domainsincart)) {
                        $domid = $domainsincart[$domain];
                        $v = $sessionCart["domains"][$domid];
                        $regperiod = $v["regperiod"];
                        if (is_array($itemdata["tlds"])) {
                            $domaintld = explode(".", $domain, 2);
                            $domaintld = "." . $domaintld[1];
                            if (!in_array($domaintld, $itemdata["tlds"])) {
                                $warnings[] = sprintf($_LANG["bundlewarningdomaintld"], implode(",", $itemdata["tlds"]), $domain);
                                $bundlesess[$bnum]["warnings"] = 1;
                            }
                        }
                        if ($itemdata["regperiod"] && $itemdata["regperiod"] != $regperiod) {
                            $warnings[] = sprintf($_LANG["bundlewarningdomainregperiod"], $itemdata["regperiod"], $domain);
                            $bundlesess[$bnum]["warnings"] = 1;
                        }
                        if (is_array($itemdata["domaddons"])) {
                            foreach ($itemdata["domaddons"] as $domaddon) {
                                if (!$v[$domaddon]) {
                                    $warnings[] = sprintf($_LANG["bundlewarningdomainaddon"], $_LANG["domain" . $domaddon], $domain);
                                    $bundlesess[$bnum]["warnings"] = 1;
                                }
                            }
                        }
                        $productbundleddomains[$domain] = array($bnum, $bitem);
                    } else {
                        if (is_array($itemdata["tlds"]) || $itemdata["regperiod"] || is_array($itemdata["domaddons"])) {
                            $warnings[] = sprintf($_LANG["bundlewarningdomainreq"], $productname);
                            $bundlesess[$bnum]["warnings"] = 1;
                        }
                    }
                }
            }
        }
        foreach ($sessionCart["domains"] as $k => $v) {
            if (isset($v["bnum"])) {
                $bnum = $v["bnum"];
                $bitem = $v["bitem"];
                $domain = $v["domain"];
                $regperiod = $v["regperiod"];
                $bid = $sessionCart["bundle"][$bnum]["bid"];
                $itemdata = $bundledata[$bid][$bitem];
                if ($itemdata["type"] != "domain") {
                    unset($sessionCart["domains"][$k]["bnum"]);
                    unset($sessionCart["domains"][$k]["bitem"]);
                } else {
                    $numitemsperbundle[$bnum]++;
                    if (is_array($itemdata["tlds"])) {
                        $domaintld = explode(".", $domain, 2);
                        $domaintld = "." . $domaintld[1];
                        if (!in_array($domaintld, $itemdata["tlds"])) {
                            $warnings[] = sprintf($_LANG["bundlewarningdomaintld"], implode(",", $itemdata["tlds"]), $domain);
                            $bundlesess[$bnum]["warnings"] = 1;
                        }
                    }
                    if ($itemdata["regperiod"] && $itemdata["regperiod"] != $regperiod) {
                        $warnings[] = sprintf($_LANG["bundlewarningdomainregperiod"], $itemdata["regperiod"], $domain);
                        $bundlesess[$bnum]["warnings"] = 1;
                    }
                    if (is_array($itemdata["addons"])) {
                        foreach ($itemdata["addons"] as $domaddon) {
                            if (!$v[$domaddon]) {
                                $warnings[] = sprintf($_LANG["bundlewarningdomainaddon"], $_LANG["domain" . $domaddon], $domain);
                                $bundlesess[$bnum]["warnings"] = 1;
                            }
                        }
                    }
                }
            }
        }
        foreach ($bundlesess as $bnum => $vals) {
            $bid = $vals["bid"];
            $bundletotalitems = count($bundledata[$bid]);
            if ($bundletotalitems != $numitemsperbundle[$bnum]) {
                unset($bundlesess[$bnum]);
            }
        }
        session(['cart' => [
            'bundle' => $bundlesess,
            'prodbundleddomains' => $productbundleddomains,
        ]]);
        return $warnings;
    }

    public static function bundlesConvertBillingCycle($cycle)
    {
        return str_replace(array("-", " "), "", strtolower($cycle));
    }

    // cart functions
    public static function bundlesGetProductPriceOverride($type, $key)
    {
        global $currency;
        $sessionCart = \Session::get('cart');
        $proddata = $sessionCart[$type . "s"][$key];
        $prodbundleddomain = false;
        if (!isset($proddata["bnum"]) && $type == "domain") {
            $domain = $proddata["domain"];
            if (isset($sessionCart["prodbundleddomains"]) && is_array($sessionCart["prodbundleddomains"][$domain])) {
                $proddata["bnum"] = $sessionCart["prodbundleddomains"][$domain][0];
                $proddata["bitem"] = $sessionCart["prodbundleddomains"][$domain][1];
            }
        }
        if (!isset($proddata["bnum"])) {
            return false;
        }
        $bid = $sessionCart["bundle"][$proddata["bnum"]]["bid"];
        if (!$bid) {
            return false;
        }
        $bundlewarnings = $sessionCart["bundle"][$proddata["bnum"]]["warnings"];
        if ($bundlewarnings) {
            return false;
        }
        $data = \App\Models\Bundle::find($bid);
        if ($data) {
            $data = $data->toArray();
        }
        $itemdata = $data ? $data["itemdata"] : "";
        $itemdata = (new \App\Helpers\Client())->safe_unserialize($itemdata);
        if ($type == "product" && $itemdata[$proddata["bitem"]]["priceoverride"]) {
            return \App\Helpers\Format::ConvertCurrency($itemdata[$proddata["bitem"]]["price"], 1, $currency["id"]);
        }
        if ($type == "domain" && $itemdata[$proddata["bitem"]]["dompriceoverride"]) {
            return \App\Helpers\Format::ConvertCurrency($itemdata[$proddata["bitem"]]["domprice"], 1, $currency["id"]);
        }
        return false;
    }
    public static function bundlesStepCompleteRedirect($lastconfig)
    {
        $i = $lastconfig["i"];
        if ($lastconfig["type"] == "product" && !session("cart.products.$i.bnum")) {
            return false;
        }
        if ($lastconfig["type"] == "domain" && !session("cart.domains.$i.bnum")) {
            return false;
        }
        if (is_array(session("cart.bundle"))) {
            $bnum = count(session("cart.bundle"));
            $bnum--;
            $bundledata = session("cart.bundle.$bnum");
            $bid = $bundledata["bid"];
            $step = $bundledata["step"];
            $complete = $bundledata["complete"];
            if (!$complete) {
                $data = \App\Models\Bundle::where(array("id" => $bid));
                $bid = $data->value("id") ?? 0;
                $itemdata = $data->value("itemdata") ?? [];
                $itemdata = (new \App\Helpers\Client)->safe_unserialize($itemdata);
                // $_SESSION["cart"]["bundle"][$bnum]["step"] = $step + 1;
                session()->put("cart.bundle.$bnum.step", $step + 1);
                // $step = $_SESSION["cart"]["bundle"][$bnum]["step"];
                $step = session("cart.bundle.$bnum.step");
                $vals = isset($itemdata[$step]) ? $itemdata[$step] : "";
                if (is_array($vals)) {
                    if ($vals["type"] == "product") {
                        $vals["bnum"] = $bnum;
                        $vals["bitem"] = $step;
                        $vals["billingcycle"] = self::bundlesconvertbillingcycle($vals["billingcycle"]);
                        // $_SESSION["cart"]["passedvariables"] = $vals;
                        session()->put("cart.passedvariables", $vals);
                        // unset($_SESSION["cart"]["lastconfigured"]);
                        session()->forget("cart.lastconfigured");
                        // redir("a=add&pid=" . $vals["pid"]);
                        return redirect()->route('cart', ['a' => 'add', 'pid' => $vals["pid"]]);
                    } else {
                        if ($vals["type"] == "domain") {
                            $vals["bnum"] = $bnum;
                            $vals["bitem"] = $step;
                            // $_SESSION["cart"]["passedvariables"] = $vals;
                            session()->put("cart.passedvariables", $vals);
                            // unset($_SESSION["cart"]["lastconfigured"]);
                            session()->forget("cart.lastconfigured");
                            // redir("a=add&domain=register");
                            return redirect()->route('cart', ['a' => 'add', 'domain' => 'register']);
                        }
                    }
                } else {
                    // $_SESSION["cart"]["bundle"][$bnum]["complete"] = 1;
                    session()->push("cart.bundle.$bnum.complete", 1);
                    // $step = $_SESSION["cart"]["bundle"][$bnum]["complete"];
                    $step = session("cart.bundle.$bnum.complete");
                }
            }
        }
    }

    public static function cartCheckIfDomainAlreadyOrdered(\App\Helpers\Domain\Domain $domainToCheck)
    {
        $existingDomains = \App\Models\Domain::where("domain", "=", $domainToCheck->getRawDomain())->whereIn("status", array("Active", "Pending", "Pending Registration", "Pending Transfer"))->get(array("domain"));
        foreach ($existingDomains as $domain) {
            if ($domain->domain == $domainToCheck->getRawDomain()) {
                return true;
            }
        }
        return false;
    }

    public static function cartAvailabilityResultsBackwardsCompat(\App\Helpers\Domain\Domain $domainToLookup, \App\Helpers\Domains\DomainLookup\SearchResult $searchResult, $matchString)
    {
        $availabilityResults = array(array("domain" => $searchResult->getDomain(), "status" => $searchResult->getStatus(), "regoptions" => $searchResult->getStatus() == $matchString ? $searchResult->pricing()->toArray() : array(), "suggestion" => false));
        $lookupProvider = \App\Helpers\Domains\DomainLookup\Provider::factory();
        foreach ($lookupProvider->getSuggestions($domainToLookup) as $suggestion) {
            $availabilityResults[] = array("domain" => $suggestion->getDomain(), "status" => $suggestion->getStatus(), "regoptions" => $suggestion->getStatus() == $matchString ? $suggestion->pricing()->toArray() : array(), "suggestion" => true);
        }
        return $availabilityResults;
    }

    public static function cartPreventDuplicateProduct($domain)
    {
        if (!$domain) {
            return true;
        }
        $domains = array();
        // foreach ($_SESSION["cart"]["products"] as $k => $values) {
        foreach (session("cart.products") ?? [] as $k => $values) {
            $domains[$k] = $values["domain"];
        }
        if (in_array($domain, $domains)) {
            $i = array_search($domain, $domains);
            // unset($_SESSION["cart"]["products"][$i]);
            session()->forget("cart.products.$i");
            // $_SESSION["cart"]["products"] = array_values($_SESSION["cart"]["products"]);
            session()->put("cart.products", array_values(session("cart.products")));
        }
    }

    public static function cartPreventDuplicateDomain($domain)
    {
        $domains = array();
        if (!empty(session("cart.domains")) && is_array(session("cart.domains"))) {
            foreach (session("cart.domains") as $k => $values) {
                $domains[$k] = $values["domain"];
            }
            if (in_array($domain, $domains)) {
                $i = array_search($domain, $domains);
                // unset($_SESSION["cart"]["domains"][$i]);
                session()->forget("cart.domains.$i");
                // $_SESSION["cart"]["domains"] = array_values($_SESSION["cart"]["domains"]);
                session()->put("cart.domains", array_values(session("cart.domains")));
            }
        }
    }

    public static function bundlesValidateProductConfig($key, $billingcycle, $configoptions, $addons)
    {
        global $_LANG;
        // $proddata = $_SESSION["cart"]["products"][$key];
        $proddata = session("cart.products.$key");
        if (!isset($proddata["bnum"])) {
            return false;
        }
        // $bid = $_SESSION["cart"]["bundle"][$proddata["bnum"]]["bid"];
        $bnum = $proddata["bnum"];
        $bid = session("cart.bundle.$bnum.bid");
        if (!$bid) {
            return false;
        }
        $data = \App\Models\Bundle::where(array("id" => $bid));
        $itemdata = $data->value("itemdata") ?? "";
        $itemdata = (new \App\Helpers\Client)->safe_unserialize($itemdata);
        $proditemdata = $itemdata[$proddata["bitem"]];
        $errors = "";
        $productname = \App\Models\Product::getProductName($proddata["pid"]);
        if ($proditemdata["billingcycle"] && self::bundlesconvertbillingcycle($proditemdata["billingcycle"]) != $billingcycle) {
            $errors .= "<li>" . sprintf($_LANG["bundlewarningproductcycle"], $proditemdata["billingcycle"], $productname);
        }
        foreach ($proditemdata["configoption"] as $cid => $opid) {
            if ($opid != $configoptions[$cid]) {
                $data = \App\Models\Productconfigoption::selectRaw("optionname,optiontype,(SELECT optionname FROM tblproductconfigoptionssub WHERE id='" . (int) $opid . "') AS subopname")->where(array("id" => $cid));
                if ($data->value("optiontype") == 1 || $data->value("optiontype") == 2) {
                    $errors .= "<li>" . sprintf($_LANG["bundlewarningproductconfopreq"], $data->value("subopname"), $data->value("optionname"));
                } else {
                    if ($data->value("optiontype") == 3) {
                        if ($opid) {
                            $errors .= "<li>" . sprintf($_LANG["bundlewarningproductconfopyesnoenable"], $data->value("optionname"));
                        } else {
                            $errors .= "<li>" . sprintf($_LANG["bundlewarningproductconfopyesnodisable"], $data->value("optionname"));
                        }
                    } else {
                        if ($data->value("optiontype") == 4) {
                            $errors .= "<li>" . sprintf($_LANG["bundlewarningproductconfopqtyreq"], $opid, $data->value("optionname"));
                        }
                    }
                }
            }
        }
        if ($proditemdata["addons"]) {
            foreach ($proditemdata["addons"] as $addonid) {
                if (!in_array($addonid, $addons)) {
                    $errors .= "<li>" . sprintf($_LANG["bundlewarningproductaddonreq"], \App\Models\Addon::where(array("id" => $addonid))->value("name") ?? "", $productname);
                }
            }
        }
        return $errors;
    }
}

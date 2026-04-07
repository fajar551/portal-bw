<?php

namespace App\Helpers\Domain;

// use Illuminate\Support\Facades\Request;
use Illuminate\Http\Request;

class Checker
{
    protected $request = NULL;
    protected $lookupProvider = NULL;
    protected $domain = NULL;
    protected $type = "";
    protected $searchResult = array();
    public function __construct(\App\Helpers\Domains\DomainLookup\Provider\AbstractProvider $lookupProvider = NULL)
    {
        $this->request = Request();
        $this->lookupProvider = $lookupProvider ?: \App\Helpers\Domains\DomainLookup\Provider::factory();
    }
    public function ajaxCheck()
    {
        // check_token();
        $this->type = $this->request->input("type", "domain");
        try {
            $source = $this->request->input("source", "");
            if ((!$source || $source != "cartAddDomain") && !in_array($this->type, array("spotlight", "suggestions"))) {
                $this->checkCaptcha();
            }
            $this->prepareAjaxDomain();
            $functionToCall = "check" . ucfirst(strtolower($this->type));
            $this->{$functionToCall}();
            $this->processPremiumDomains();
        } catch (\Exception $e) {
            // dd($e);
            $this->searchResult = array("error" => $e->getMessage());
        }
        $this->conditionallyReleaseSession();
        $result = $this->searchResult;
        if ($result instanceof \App\Helpers\Domains\DomainLookup\ResultsList) {
            $result = $result->toArray();
        }
        $response = new \Illuminate\Http\JsonResponse(array("result" => $result), 200, array("Content-Type" => "application/json"));
        $response->send();
        \App\Helpers\Termius::getInstance()->doExit();
    }
    public function cartDomainCheck(\App\Helpers\Domain\Domain $searchDomain, array $tlds)
    {
        $this->domain = $searchDomain;
        $this->searchResult = $this->lookupProvider->checkAvailability($searchDomain, $tlds);
    }
    public function getLookupProvider()
    {
        return $this->lookupProvider;
    }
    public function getSearchResult()
    {
        return $this->searchResult;
    }
    public function populateSuggestionsInSmartyValues(array &$smartyVariables)
    {
        $suggestions = $this->lookupProvider->getSuggestions($this->domain);
        $otherSuggestions = array();
        $smartyVariables["searchResults"]["suggestions"] = array();
        foreach ($suggestions as $suggestion) {
            $smartyVariables["searchResults"]["suggestions"][] = $suggestion->toArray();
            $otherSuggestions[] = array("domain" => $suggestion->getDomain(), "status" => $suggestion->getStatus(), "regoptions" => $suggestion->pricing()->toArray());
        }
        $smartyVariables["othersuggestions"] = $otherSuggestions;
    }
    protected function conditionallyReleaseSession()
    {
        switch ($this->type) {
            case "incart":
            case "owndomain":
            case "subdomain":
                break;
            default:
                // \Session::release();
                session_write_close();
        }
    }
    protected function checkCaptcha()
    {
        // if (\Session::get("CaptchaComplete") === true) {
        //     return NULL;
        // }
        // $captcha = new \WHMCS\Utility\Captcha();
        // if ($captcha->isEnabled() && !$captcha->recaptcha->isInvisible() && \Session::get("CaptchaComplete") !== true) {
        //     throw new \WHMCS\Exception(\Lang::trans("googleRecaptchaIncorrect"));
        // }
        // $validate = new \WHMCS\Validate();
        // $captcha->validateAppropriateCaptcha(\WHMCS\Utility\Captcha::FORM_DOMAIN_CHECKER, $validate);
        // if ($validate->hasErrors()) {
        //     throw new \WHMCS\Exception($validate->getErrors()[0]);
        // }
        // \Session::set("CaptchaComplete", true);
    }
    protected function processIdnLabel($label)
    {
        $label = \App\Helpers\Cfg::getValue("AllowIDNDomains") ? mb_strtolower($label) : strtolower($label);
        $label = str_replace(array("'", "+", ",", "|", "!", "\\", "\"", "£", "\$", "%", "&", "/", "(", ")", "=", "?", "^", "*", " ", "°", "§", ";", ":", "_", "<", ">", "]", "[", "@", ")"), "", $label);
        return $label;
    }
    protected function prepareAjaxDomain()
    {
        if ($this->request->has("sld") && $this->request->has("tld")) {
            $sld = $this->processIdnLabel(\App\Helpers\Sanitize::decode($this->request->input("sld")));
            $tld = $this->processIdnLabel(\App\Helpers\Sanitize::decode($this->request->input("tld")));
            $this->domain = \App\Helpers\Domain\Domain::createFromSldAndTld($sld, $tld);
        } else {
            $this->domain = new \App\Helpers\Domain\Domain($this->processIdnLabel(\App\Helpers\Sanitize::decode($this->request->input("domain"))));
        }
    }
    protected function checkDomain()
    {
        $validate = new \App\Helpers\Validate();
        $validate->validate("unique_domain", "unique_domain", "client.ordererrordomainalreadyexists", "", $this->domain);
        \App\Helpers\Hooks::run_validate_hook($validate, "ShoppingCartValidateDomain", array("domainoption" => "register", "sld" => $this->domain->getSecondLevel(), "tld" => $this->domain->getDotTopLevel()));
        if ($validate->hasErrors()) {
            $errors = "";
            foreach ($validate->getErrors() as $error) {
                $errors .= $error . "<br />";
            }
            $this->searchResult = array("error" => $errors);
            return NULL;
        } else {
            $originalTld = $tld = $this->domain->getDotTopLevel();
            $tlds = $this->getTldsList();
            $preferredTLDNotAvailable = false;
            if ($tld == "." || !in_array($tld, $tlds)) {
                if ($tld != ".") {
                    $preferredTLDNotAvailable = true;
                }
                $tld = $tlds[0];
            }
            $this->cartDomainCheck($this->domain, array($tld));
            $searchResult = $this->getSearchResult();
            if ($searchResult instanceof \App\Helpers\Domains\DomainLookup\ResultsList) {
                $searchResult = $searchResult->toArray();
            }
            if ($preferredTLDNotAvailable) {
                $searchResult[0]["preferredTLDNotAvailable"] = $preferredTLDNotAvailable;
                $searchResult[0]["originalUnavailableDomain"] = $searchResult[0]["sld"] . $originalTld;
            }
            $this->searchResult = $searchResult;
        }
    }
    protected function checkIncart()
    {
        $orderForm = new \App\Helpers\OrderForm();
        $productId = (int) $this->request->input("pid");
        $productInfo = $orderForm->setPid($productId);
        // $passedVariables = $_SESSION["cart"]["passedvariables"];
        $passedVariables = session("cart.passedvariables");
        // unset($_SESSION["cart"]["passedvariables"]);
        session()->forget("cart.passedvariables");
        $this->cartPreventDuplicateProduct($this->domain->getDomain());
        $productArray = array(
            "pid" => $productId,
            "domain" => $this->domain->getDomain(),
            "billingcycle" => isset($passedVariables["billingcycle"]) ? $passedVariables["billingcycle"] : $orderForm->validateBillingCycle(""),
            "configoptions" => $passedVariables["configoption"] ?? [],
            "customfields" => $passedVariables["customfield"] ?? [],
            "addons" => $passedVariables["addons"] ?? [],
            "server" => "",
            "noconfig" => true,
            "skipConfig" => isset($passedVariables["skipconfig"]) && $passedVariables["skipconfig"]
        );
        if (isset($passedVariables["bnum"])) {
            $productArray["bnum"] = $passedVariables["bnum"];
        }
        if (isset($passedVariables["bitem"])) {
            $productArray["bitem"] = $passedVariables["bitem"];
        }
        // $_SESSION["cart"]["newproduct"] = true;
        session()->put("cart.newproduct", true);
        $updatedExistingQuantity = false;
        if ($productInfo["allowqty"]) {
            // foreach ($_SESSION["cart"]["products"] as &$cart_prod) {
            foreach (session("cart.products") as &$cart_prod) {
                if ($productId == $cart_prod["pid"]) {
                    if (empty($cart_prod["qty"])) {
                        $cart_prod["qty"] = 1;
                    }
                    $cart_prod["qty"]++;
                    if ($productInfo["stockcontrol"] && $productInfo["qty"] < $cart_prod["qty"]) {
                        $cart_prod["qty"] = $productInfo["qty"];
                    }
                    $updatedExistingQuantity = true;
                    break;
                }
            }
        }
        if (!$updatedExistingQuantity) {
            // $_SESSION["cart"]["products"][] = $productArray;
            session()->push("cart.products", $productArray);
        }
        // $newProductIValue = count($_SESSION["cart"]["products"]) - 1;
        $newProductIValue = count(session("cart.products")) - 1;
        if (isset($passedVariables["skipconfig"]) && $passedVariables["skipconfig"]) {
            // unset($_SESSION["cart"]["products"][$newProductIValue]["noconfig"]);
            session()->forget("cart.products.$newProductIValue.noconfig");
            // $_SESSION["cart"]["lastconfigured"] = array("type" => "product", "i" => $newProductIValue);
            session()->put("cart.lastconfigured", array("type" => "product", "i" => $newProductIValue));
        }
        $searchResult[] = array("status" => true, "num" => $newProductIValue);
        $this->searchResult = $searchResult;
    }
    protected function checkOwndomain()
    {
        $this->lookupProvider->checkOwnDomain($this->domain);
        $this->checkIncart();
    }
    protected function checkSpotlight()
    {
        $spotlightTlds = $this->getSpotlightTlds();
        $searchResult = new \App\Helpers\Domains\DomainLookup\ResultsList();
        if (0 < count($spotlightTlds)) {
            $searchResult = $this->lookupProvider->checkAvailability($this->domain, $spotlightTlds);
        }
        $this->searchResult = $searchResult;
    }
    protected function checkSubdomain()
    {
        $this->lookupProvider->checkSubDomain($this->domain);
        $this->checkIncart();
    }
    protected function checkSuggestions()
    {
        $this->searchResult = $this->lookupProvider->getSuggestions($this->domain);
    }
    protected function checkTransfer()
    {
        $this->overrideCheckIfDomainAlreadyOrdered();
        if (empty($this->searchResult)) {
            $tld = $this->domain->getDotTopLevel();
            $this->searchResult = $this->lookupProvider->checkAvailability($this->domain, array($tld));
        }
    }
    protected function overrideCheckIfDomainAlreadyOrdered()
    {
        if (\App\Helpers\Cart::cartCheckIfDomainAlreadyOrdered($this->domain)) {
            $errorResult = new \App\Helpers\Domains\DomainLookup\SearchResult($this->domain->getSecondLevel(), $this->domain->getTopLevel());
            $errorResult->setStatus(\App\Helpers\Domains\DomainLookup\SearchResult::STATUS_UNKNOWN);
            $this->searchResult = $errorResult;
        }
    }
    protected function processPremiumDomains()
    {
        if (\App\Helpers\Cfg::getValue("PremiumDomains")) {
            $premiumSessionData = array();
            foreach ($this->searchResult as $key => $domain) {
                if (is_object($domain)) {
                    $domain = $domain->toArray();
                }
                if ($domain["isPremium"]) {
                    $premiumSessionData[$domain["domainName"]] = array("markupPrice" => $domain["pricing"], "cost" => $domain["premiumCostPricing"]);
                }
            }
            if ($premiumSessionData) {
                $storedSessionData = \Session::get("PremiumDomains");
                if ($storedSessionData && is_array($storedSessionData)) {
                    $premiumSessionData = array_merge($storedSessionData, $premiumSessionData);
                }
                \Session::setAndRelease("PremiumDomains", $premiumSessionData);
            }
        }
    }
    protected function cartPreventDuplicateProduct($domain)
    {
        if ($domain) {
            $domains = array();
            // foreach ($_SESSION["cart"]["products"] as $k => $values) {
            foreach (session("cart.products") ?? [] as $k => $values) {
                $domains[$k] = $values["domain"];
            }
            if (in_array($domain, $domains)) {
                $i = array_search($domain, $domains);
                if ($i !== false) {
                    // unset($_SESSION["cart"]["products"][$i]);
                    session()->forget("cart.products.$i");
                    // $_SESSION["cart"]["products"] = array_values($_SESSION["cart"]["products"]);
                    session()->put("cart.products", array_values(session("cart.products")));
                }
            }
        }
    }
    public function populateCartWithDomainSmartyVariables($domainOption, array &$smartyVariables)
    {
        $searchResult = $this->searchResult[0];
        if ($domainOption == "register") {
            $matchString = \App\Helpers\Domains\DomainLookup\SearchResult::STATUS_NOT_REGISTERED;
        } else {
            $matchString = \App\Helpers\Domains\DomainLookup\SearchResult::STATUS_REGISTERED;
        }
        if ($searchResult->getStatus() == \App\Helpers\Domains\DomainLookup\SearchResult::STATUS_UNKNOWN) {
            $matchString = \App\Helpers\Domains\DomainLookup\SearchResult::STATUS_UNKNOWN;
        }
        $smartyVariables["searchvar"] = $matchString;
        $smartyVariables["searchResults"] = $searchResult->toArray();
        $smartyVariables["availabilityresults"] = \App\Helpers\Cart::cartAvailabilityResultsBackwardsCompat($this->domain, $searchResult, $matchString);
    }
    protected function getTldsList()
    {
        return \App\Helpers\DomainFunctions::getTLDList();
    }
    protected function getSpotlightTlds()
    {
        return \App\Helpers\DomainFunctions::getSpotlightTlds();
    }
}

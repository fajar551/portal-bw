<?php

namespace App\Helpers\Domains\DomainLookup\Provider\WhmcsDomains;

class ApiClient
{
    private $url = "";
    private $apiKey = "";
    private $timeout = 3;
    private $httpClient = NULL;
    private $language = self::DEFAULT_LANGUAGE;
    private $enableSensitiveContentFilter = true;
    const DEFAULT_LANGUAGE = "english";
    const SUPPORTED_LANGUAGES = array("english" => "eng", "french" => "fre", "spanish" => "spa", "italian" => "ita", "portuguese" => "por", "german" => "ger", "dutch" => "dut", "turkish" => "tur", "vietnamese" => "vie", "chinese" => "chi", "japanese" => "jpn", "korean" => "kor");
    public function __construct($url = NULL, $apiKey = NULL)
    {
        if (!$url) {
            $url = $this->getDefaultUrl();
        }
        $this->setUrl($url);
        if ($apiKey) {
            $this->setApiKey($apiKey);
        }
    }
    public function getLanguage()
    {
        return $this->language;
    }
    public function getEnableSensitiveContentFilter()
    {
        return $this->enableSensitiveContentFilter;
    }
    public function setEnableSensitiveContentFilter($enableSensitiveContentFilter)
    {
        $this->enableSensitiveContentFilter = $enableSensitiveContentFilter;
        return $this;
    }
    public function setLanguage($language)
    {
        $language = strtolower($language);
        if (array_key_exists($language, self::SUPPORTED_LANGUAGES)) {
            $this->language = $language;
        }
        return $this;
    }
    private function getDefaultUrl()
    {
        return "https://domains.whmcs.net";
    }
    public function getUrl()
    {
        return $this->url;
    }
    private function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }
    public function getApiKey()
    {
        return $this->apiKey;
    }
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
        return $this;
    }
    private function getWhmcsAuthzToken()
    {
        // $time = time();
        // $license = \DI::make("license");
        // return $license->hashMessage($time);
        return "";
    }
    private function getHttpClientCustomHeaders()
    {
        if ($this->getApiKey()) {
            $custom["X-NAMESUGGESTION-APIKEY"] = $this->getApiKey();
        } else {
            $token = explode("|", $this->getWhmcsAuthzToken(), 2);
            list($custom["X-WHMCS-ID"], $custom["X-WHMCS-AUTHZ-TOKEN"]) = $token;
        }
        return $custom;
    }
    private function getHttpClient()
    {
        if (is_null($this->httpClient)) {
            $this->httpClient = new \GuzzleHttp\Client(array("base_url" => $this->getUrl(), "timeout" => $this->getTimeout()));
        }
        return $this->httpClient;
    }
    public function getTimeout()
    {
        return $this->timeout;
    }
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }
    protected function apiCall($endpoint, array $params = array())
    {
        try {
            $client = $this->getHttpClient();
            $headers = array_merge(array("Accept" => "application/json"), $this->getHttpClientCustomHeaders());
            $response = (string) $client->get($endpoint, array("query" => $params, "headers" => $headers))->getBody();
        } catch (\Exception $e) {
            throw new \App\Helpers\Domains\DomainLookup\DomainLookupException($e->getMessage());
        }
        $results = json_decode($response, true);
        if (!is_array($results) || !isset($results["results"])) {
            throw new \App\Helpers\Domains\DomainLookup\DomainLookupException(is_array($results) && isset($results["message"]) ? $results["message"] : "Invalid response");
        }
        return $results["results"];
    }
    protected function getSearchResult(array $apiDomainResult)
    {
        $domainName = strtolower($apiDomainResult["name"]);
        $availability = strtolower($apiDomainResult["availability"]);
        $domain = new \App\Helpers\Domain\Domain($domainName);
        $searchResultStatus = \App\Helpers\Domains\DomainLookup\SearchResult::STATUS_UNKNOWN;
        switch ($availability) {
            case "available":
                $searchResultStatus = \App\Helpers\Domains\DomainLookup\SearchResult::STATUS_NOT_REGISTERED;
                break;
            case "registered":
                $searchResultStatus = \App\Helpers\Domains\DomainLookup\SearchResult::STATUS_REGISTERED;
                break;
            case "reserved":
                $searchResultStatus = \App\Helpers\Domains\DomainLookup\SearchResult::STATUS_RESERVED;
                break;
            case "unknown":
            case "invalid":
                $searchResultStatus = \App\Helpers\Domains\DomainLookup\SearchResult::STATUS_UNKNOWN;
                break;
            case "unsupported":
                $searchResultStatus = \App\Helpers\Domains\DomainLookup\SearchResult::STATUS_TLD_NOT_SUPPORTED;
                break;
        }
        $domain->setGeneralAvailability($searchResultStatus === \App\Helpers\Domains\DomainLookup\SearchResult::STATUS_NOT_REGISTERED);
        $searchResult = \App\Helpers\Domains\DomainLookup\SearchResult::factoryFromDomain($domain);
        $searchResult->setStatus($searchResultStatus);
        return $searchResult;
    }
    public function bulkCheck($sld, array $tlds)
    {
        if (!is_array($sld)) {
            $sld = array($sld);
        }
        $apiResults = $this->apiCall("bulk_check", array("names" => implode(",", $sld), "tlds" => implode(",", $tlds), "include-registered" => "true"));
        if (empty($apiResults)) {
            $apiResults = array();
            foreach ($sld as $singleSld) {
                foreach ($tlds as $singleTld) {
                    $apiResults[] = array("name" => $singleSld . "." . trim($singleTld, "."), "availability" => "invalid");
                }
            }
        }
        $searchResults = array();
        foreach ($apiResults as $apiResult) {
            $searchResults[] = $this->getSearchResult($apiResult);
        }
        return $searchResults;
    }
    protected function getApiLanguage()
    {
        if (!array_key_exists($this->language, self::SUPPORTED_LANGUAGES)) {
            return self::DEFAULT_LANGUAGE;
        }
        return self::SUPPORTED_LANGUAGES[$this->language];
    }
    public function suggest($name, array $tlds, $maxResults = 20)
    {
        $params = array("name" => $name, "tlds" => implode(",", $tlds), "lang" => $this->getApiLanguage(), "max-results" => $maxResults, "sensitive-content-filter" => $this->enableSensitiveContentFilter ? "true" : "false");
        $geotargetingEnabled = \App\Helpers\Domains\DomainLookup\Settings::ofRegistrar("WhmcsWhois")->where("setting", "geotargetedResults")->first();
        if ($geotargetingEnabled && $geotargetingEnabled->value) {
            $userIp = \Request::ip();
            $flags = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
            if (filter_var($userIp, FILTER_VALIDATE_IP, $flags)) {
                $params["ip-address"] = $userIp;
            }
        }
        $apiResults = $this->apiCall("suggest", $params);
        $searchResults = array();
        foreach ($apiResults as $apiResult) {
            $searchResults[] = $this->getSearchResult($apiResult);
        }
        return $searchResults;
    }
}

?>

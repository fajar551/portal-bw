<?php


namespace App\Helpers\Domains\DomainLookup\Provider;

class WhmcsDomains extends WhmcsWhois
{
    private $whmcsApiClient = NULL;
    private $settings = array();
    public function __construct(WhmcsDomains\ApiClient $apiClient = NULL)
    {
        // $config = \DI::make("config");
        $config = \Config::get("portal");
        if (!$config["disable_whmcs_domain_lookup"]) {
            if (!$apiClient) {
                $url = $config["domain_lookup_url"];
                $key = $config["domain_lookup_key"];
                $apiClient = new WhmcsDomains\ApiClient($url, $key);
            }
            $this->whmcsApiClient = $apiClient;
            $class = "Lang";
            if (\App\Helpers\Application::isClientAreaRequest() || defined("ADMINAREA")) {
                $class = "Lang";
            }
            $this->settings = \App\Helpers\Domains\DomainLookup\Settings::ofRegistrar("WhmcsWhois")->pluck("value", "setting")->toArray();
            // $this->whmcsApiClient->setLanguage($class::getName())->setEnableSensitiveContentFilter((bool) empty($this->settings["suggestAdultDomains"]));
            $this->whmcsApiClient->setLanguage('english')->setEnableSensitiveContentFilter((bool) empty($this->settings["suggestAdultDomains"]));
        }
    }
    protected function processUnknownResults($sld, array $verisignResults)
    {
        $knownResults = array();
        $unknownResults = array();
        foreach ($verisignResults as $result) {
            if (in_array($result->getStatus(), array(\App\Helpers\Domains\DomainLookup\SearchResult::STATUS_UNKNOWN, \App\Helpers\Domains\DomainLookup\SearchResult::STATUS_TLD_NOT_SUPPORTED))) {
                $unknownResults[] = $result;
            } else {
                $knownResults[] = $result;
            }
        }
        if (!empty($unknownResults)) {
            $unknownTlds = array_map(function (\App\Helpers\Domains\DomainLookup\SearchResult $result) {
                return $result->getTopLevel();
            }, $unknownResults);
            $unknownResults = parent::getGeneralAvailability($sld, $unknownTlds)->getArrayCopy();
        }
        return new \App\Helpers\Domains\DomainLookup\ResultsList(array_merge($knownResults, $unknownResults));
    }
    protected function getGeneralAvailabilityViaApi($sld, array $tlds)
    {
        return $this->whmcsApiClient->bulkCheck($sld, $tlds);
    }
    protected function getGeneralAvailability($sld, array $tlds)
    {
        if ($this->whmcsApiClient) {
            $tlds = array_filter($tlds);
            if (empty($tlds)) {
                return new \App\Helpers\Domains\DomainLookup\ResultsList();
            }
            try {
                $verisignResults = $this->getGeneralAvailabilityViaApi($sld, $tlds);
                return $this->processUnknownResults($sld, $verisignResults);
            } catch (\Exception $e) {
                return parent::getGeneralAvailability($sld, $tlds);
            }
        } else {
            return parent::getGeneralAvailability($sld, $tlds);
        }
    }
    protected function getDomainSuggestionsViaApi(\App\Helpers\Domain\Domain $domain, $tldsToInclude)
    {
        return $this->whmcsApiClient->suggest($domain->getSecondLevel(), $tldsToInclude, 100);
    }
    protected function getDomainSuggestions(\App\Helpers\Domain\Domain $domain, $tldsToInclude)
    {
        if ($this->whmcsApiClient) {
            $results = array();
            $tldsToInclude = array_filter($tldsToInclude);
            if (!empty($tldsToInclude)) {
                try {
                    $tldsToInclude = $this->preprocessDomainSuggestionTlds($tldsToInclude);
                    $results = $this->getDomainSuggestionsViaApi($domain, $tldsToInclude);
                } catch (\Exception $e) {
                    return parent::getDomainSuggestions($domain, $tldsToInclude);
                }
            }
            return new \App\Helpers\Domains\DomainLookup\ResultsList($results);
        }
        return parent::getDomainSuggestions($domain, $tldsToInclude);
    }
    public function getSettings()
    {
        $settings = array_merge(parent::getSettings(), array("geotargetedResults" => array("FriendlyName" => \Lang::get("admin.generalgeotargetedresults"), "Type" => "yesno", "Description" => "", "Default" => true)));
        return $settings;
    }
}

?>

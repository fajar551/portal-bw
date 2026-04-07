<?php

namespace App\Helpers\Domains\DomainLookup\Provider;
use Illuminate\Support\Facades\Log;
use App\Helpers\LogActivity;


class BasicWhois extends AbstractProvider
{
    protected $bulkCheckLimit = 10;
    protected static $apiClient = NULL;
    protected function getGeneralAvailability($sld, array $tlds)
    {
        $domainSearchResults = new \App\Helpers\Domains\DomainLookup\ResultsList();
        $count = 1;
        foreach ($tlds as $tld) {
            $domainSearchResult = new \App\Helpers\Domains\DomainLookup\SearchResult($sld, $tld);
            $tld = $domainSearchResult->getDotTopLevel();
            if ($count <= $this->bulkCheckLimit) {
                $api = $this->factoryApiClient();
                $apiResult = $api->lookup(array("sld" => $sld, "tld" => $tld));
                if ($apiResult["result"] == "available") {
                    $domainSearchResult->setStatus($domainSearchResult::STATUS_NOT_REGISTERED);
                } else {
                    if ($apiResult["result"] == "unavailable") {
                        if (!empty($apiResult["whois"]) && strpos($apiResult["whois"], "Right of registration:") !== false) {
                            $domainSearchResult->setStatus($domainSearchResult::STATUS_RESERVED);
                        } else {
                            $domainSearchResult->setStatus($domainSearchResult::STATUS_REGISTERED);
                        }
                    } else {
                        // if ($apiResult["result"] == "error") {
                        //     LogActivity::Save(sprintf("WHOIS Lookup Error for '%s': %s", $domainSearchResult->getDomain(), $apiResult["errordetail"] ? $apiResult["errordetail"] : "error detail unknown"));
                        //     $domainSearchResult->setStatus($domainSearchResult::STATUS_UNKNOWN);
                        // } else {
                            if ($apiResult["result"] == "error") {
                                $errorDetail = isset($apiResult["errordetail"]) ? $apiResult["errordetail"] : "error detail unknown";
                                LogActivity::Save(sprintf(
                                    "WHOIS Lookup Error for '%s': %s",
                                    $domainSearchResult->getDomain(),
                                    $errorDetail
                                ));
                                $domainSearchResult->setStatus($domainSearchResult::STATUS_UNKNOWN);
                            } else {
                            LogActivity::Save(sprintf("WHOIS Lookup Error for '%s': %s", $domainSearchResult->getDomain(), "extension not listed in /resources/domains/dist.whois.json or /resources/domains/whois.json"));
                            $domainSearchResult->setStatus($domainSearchResult::STATUS_UNKNOWN);
                        }
                    }
                }
                $count++;
            } else {
                $domainSearchResult->setStatus($domainSearchResult::STATUS_NOT_REGISTERED);
            }
            $domainSearchResults->append($domainSearchResult);
        }
        return $domainSearchResults;
    }
    protected function preprocessDomainSuggestionTlds(array $tldsToInclude)
    {
        $spotlightTlds = $this->getSpotlightTlds();
        $tldsToInclude = array_filter($tldsToInclude, function ($tld) use($spotlightTlds) {
            if (in_array("." . $tld, $spotlightTlds)) {
                return false;
            }
            return true;
        });
        return $tldsToInclude;
    }
    protected function getDomainSuggestions(\App\Helpers\Domain\Domain $domain, $tldsToInclude)
    {
        $tldsToInclude = $this->preprocessDomainSuggestionTlds($tldsToInclude);
        $results = $this->checkAvailability($domain, $tldsToInclude);
        foreach ($results as $key => $result) {
            $result = $result->toArray();
            if ($result["isRegistered"] || !$result["isValidDomain"]) {
                unset($results[$key]);
            }
        }
        return $results;
    }
    public function factoryApiClient()
    {
        if (!static::$apiClient) {
            $whois = new \App\Helpers\WHOIS();
            static::$apiClient = $whois;
        }
        return static::$apiClient;
    }
    public function getSettings()
    {
        return array();
    }
}

?>

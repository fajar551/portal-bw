<?php

namespace App\Helpers\Domains\DomainLookup\Provider;

abstract class AbstractProvider
{
    protected abstract function getGeneralAvailability($sld, array $tlds);
    protected abstract function getDomainSuggestions(\App\Helpers\Domain\Domain $domain, $tldsToInclude);
    public abstract function getSettings();
    public function checkAvailability(\App\Helpers\Domain\Domain $domain, $tlds)
    {
        $resultsList = $this->getGeneralAvailability($domain->getIdnSecondLevel(), $tlds);
        if (!$resultsList instanceof \App\Helpers\Domains\DomainLookup\ResultsList) {
            throw new \InvalidArgumentException("Return must be an instance of \\App\\Helpers\\Domains\\DomainLookup\\ResultsList");
        }
        return $resultsList;
    }
    protected function getSpotlightTlds()
    {
        return \App\Helpers\DomainFunctions::getSpotlightTlds();
    }
    public function getSuggestions(\App\Helpers\Domain\Domain $domain)
    {
        $resultsList = $this->getDomainSuggestions($domain, $this->getTldsForSuggestions());
        if (!$resultsList instanceof \App\Helpers\Domains\DomainLookup\ResultsList) {
            throw new \InvalidArgumentException("Return must be an instance of \\App\\Helpers\\Domains\\DomainLookup\\ResultsList");
        }
        $spotlightDomains = array();
        foreach ($this->getSpotlightTlds() as $tld) {
            $spotlightDomains[] = $domain->getSecondLevel() . $tld;
        }
        $shownElsewhere = array_merge(array($domain->getDomain()), $spotlightDomains);
        $list = $resultsList->toArray();
        foreach ($list as $key => $result) {
            if (in_array($result["domainName"], $shownElsewhere)) {
                $resultsList->offsetUnset($key);
            } else {
                if (!$result["isValidDomain"]) {
                    $resultsList->offsetUnset($key);
                }
            }
        }
        $resultsList->uasort(function (\App\Helpers\Domains\DomainLookup\SearchResult $firstResult, \App\Helpers\Domains\DomainLookup\SearchResult $secondResult) {
            $scoreA = round($firstResult->getScore(), 3);
            $scoreB = round($secondResult->getScore(), 3);
            if ($scoreA === $scoreB) {
                return 0;
            }
            return $scoreB < $scoreA ? -1 : 1;
        });
        return $resultsList;
    }
    public function getTldsForSuggestions()
    {
        $setting = \App\Helpers\Domains\DomainLookup\Settings::ofRegistrar("WhmcsWhois")->whereSetting("suggestTlds")->first();
        if (!$setting) {
            return array();
        }
        $settingTlds = explode(",", $setting->value);
        $qualifiedTlds = \App\Helpers\DomainFunctions::getTLDList("register");
        $suggestedTlds = array_intersect($settingTlds, $qualifiedTlds);
        return array_values(array_filter(array_map(function ($tld) {
            return ltrim($tld, ".");
        }, $suggestedTlds)));
    }
    public function checkSubDomain(\App\Helpers\Domain\Domain $subDomain)
    {
        if (!\App\Helpers\Domain\Domain::isValidDomainName($subDomain->getSecondLevel(), ".com")) {
            throw new \App\Exceptions\InvalidDomain("ordererrordomaininvalid");
        }
        $bannedSubDomainPrefixes = explode(",", \App\Helpers\Cfg::getValue("BannedSubdomainPrefixes"));
        if (in_array($subDomain->getSecondLevel(), $bannedSubDomainPrefixes)) {
            throw new \App\Exceptions\InvalidDomain("ordererrorsbudomainbanned");
        }
        if (\App\Helpers\Cfg::getValue("AllowDomainsTwice")) {
            $subChecks = DB::table("tblhosting")->where("domain", "=", $subDomain->getSecondLevel() . $subDomain->getDotTopLevel())->whereNotIn("domainstatus", array("Terminated", "Cancelled", "Fraud"))->count();
            if ($subChecks) {
                throw new \App\Exceptions\InvalidDomain("ordererrorsubdomaintaken");
            }
        }
        $validate = new \App\Helpers\Validate();
        \App\Helpers\Hooks::run_validate_hook($validate, "CartSubdomainValidation", array("subdomain" => $subDomain->getSecondLevel(), "domain" => $subDomain->getDotTopLevel()));
        if ($validate->hasErrors()) {
            $errors = "";
            foreach ($validate->getErrors() as $error) {
                $errors .= $error . "<br />";
            }
            throw new \App\Exceptions\InvalidDomain($errors);
        }
    }
    public function checkOwnDomain(\App\Helpers\Domain\Domain $ownDomain)
    {
        if (!\App\Helpers\Domain\Domain::isValidDomainName($ownDomain->getSecondLevel(), $ownDomain->getDotTopLevel())) {
            throw new \App\Exceptions\InvalidDomain("ordererrordomaininvalid");
        }
        if (!\App\Helpers\Domain\Domain::isSupportedTld($ownDomain->getDotTopLevel())) {
            throw new \App\Exceptions\InvalidDomain("ordererrordomaininvalid");
        }
        if (\App\Helpers\Cfg::getValue("AllowDomainsTwice")) {
            $subChecks = DB::table("tblhosting")->where("domain", "=", $ownDomain->getSecondLevel() . $ownDomain->getDotTopLevel())->whereNotIn("domainstatus", array("Terminated", "Cancelled", "Fraud"))->count();
            if ($subChecks) {
                throw new \App\Exceptions\InvalidDomain("ordererrordomainalreadyexists");
            }
        }
        $validate = new \App\Helpers\Validate();
        \App\Helpers\Hooks::run_validate_hook($validate, "ShoppingCartValidateDomain", array("domainoption" => "owndomain", "sld" => $ownDomain->getSecondLevel(), "tld" => $ownDomain->getDotTopLevel()));
        if ($validate->hasErrors()) {
            $errors = "";
            foreach ($validate->getErrors() as $error) {
                $errors .= $error . "<br />";
            }
            throw new \App\Exceptions\InvalidDomain($errors);
        }
    }
    public function getProviderName()
    {
        return str_replace("App\\Helpers\\Domains\\DomainLookup\\Provider\\", "", get_class($this));
    }
}

?>

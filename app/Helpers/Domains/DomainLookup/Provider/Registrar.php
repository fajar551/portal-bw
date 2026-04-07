<?php

namespace App\Helpers\Domains\DomainLookup\Provider;

class Registrar extends WhmcsWhois
{
    protected $registrarModule = NULL;
    protected function getGeneralAvailability($sld, array $tlds)
    {
        try {
            $domain = new \App\Helpers\Domain\Domain($sld);
            $domainSearchResults = $this->getRegistrar()->call("CheckAvailability", array("sld" => $sld, "tlds" => $tlds, "searchTerm" => $domain->getSecondLevel(), "tldsToInclude" => $tlds, "isIdnDomain" => $domain->isIdn(), "punyCodeSearchTerm" => $domain->isIdn() ? $domain->getIdnSecondLevel() : "", "premiumEnabled" => (bool) (int) \App\Helpers\Cfg::getValue("PremiumDomains")));
            foreach ($domainSearchResults as $key => $domainSearchResult) {
                if ($domainSearchResult->getStatus() == $domainSearchResult::STATUS_TLD_NOT_SUPPORTED) {
                    $unsupportedTld = $domainSearchResult->getDotTopLevel();
                    $tldNotSupportedByEnom = parent::getGeneralAvailability($sld, array($unsupportedTld));
                    $domainSearchResult->setStatus($tldNotSupportedByEnom->offsetGet(0)->getStatus());
                }
            }
            return $domainSearchResults;
        } catch (\Exception $e) {
            return parent::getGeneralAvailability($sld, $tlds);
        }
    }
    protected function getDomainSuggestions(\App\Helpers\Domain\Domain $domain, $tldsToInclude)
    {
        try {
            $settings = \App\Helpers\Domains\DomainLookup\Settings::ofRegistrar($this->registrarModule->getLoadedModule())->pluck("value", "setting")->toArray();
            return $this->getRegistrar()->call("GetDomainSuggestions", array("searchTerm" => $domain->getSecondLevel(), "tldsToInclude" => $tldsToInclude, "isIdnDomain" => $domain->isIdn(), "punyCodeSearchTerm" => $domain->isIdn() ? $domain->getIdnSecondLevel() : "", "suggestionSettings" => $settings, "premiumEnabled" => (bool) (int) \App\Helpers\Cfg::getValue("PremiumDomains")));
        } catch (\Exception $e) {
            return parent::getDomainSuggestions($domain, $tldsToInclude);
        }
    }
    public function getTldsForSuggestions()
    {
        $tlds = \App\Helpers\DomainFunctions::getTLDList("register");
        if (!is_array($tlds) || empty($tlds)) {
            return array();
        }
        $cleanTlds = array_values(array_filter(array_map(function ($tld) {
            return ltrim($tld, ".");
        }, $tlds)));
        return $cleanTlds;
    }
    public function loadRegistrar($moduleName)
    {
        $this->registrarModule = new \App\Module\Registrar();
        return $this->registrarModule->load($moduleName);
    }
    public function getRegistrar()
    {
        return $this->registrarModule;
    }
    public function getSettings()
    {
        $result = $this->registrarModule->call("DomainSuggestionOptions");
        if (!is_array($result)) {
            return array();
        }
        return $result;
    }
}

?>

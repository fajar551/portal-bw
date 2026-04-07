<?php

namespace App\Helpers\Domains\DomainLookup;

class Provider
{
    protected static $providerNames = array("BasicWhois" => "\\App\\Helpers\\Domains\\DomainLookup\\Provider\\BasicWhois", "WhmcsWhois" => "\\App\\Helpers\\Domains\\DomainLookup\\Provider\\WhmcsWhois", "WhmcsDomains" => "\\App\\Helpers\\Domains\\DomainLookup\\Provider\\WhmcsDomains", "Registrar" => "\\App\\Helpers\\Domains\\DomainLookup\\Provider\\Registrar");
    public static function factory($providerName = "", $registrar = "")
    {
        if (empty($providerName)) {
            $providerName = static::getDomainLookupProvider();
        }
        $providerClassMap = static::getAvailableProviders();
        $className = $providerClassMap[$providerName];
        $provider = new $className();
        if (!$provider instanceof Provider\AbstractProvider) {
            throw new \App\Exceptions\Information("Domain lookup provider '" . $providerName . "' must implement " . "App\\Helpers\\Domains\\DomainLookup\\Provider\\AbstractProvider");
        }
        if ($provider instanceof Provider\Registrar) {
            if (empty($registrar)) {
                $registrar = static::getDomainLookupRegistrar();
            }
            if (!$provider->loadRegistrar($registrar)) {
                $provider = static::factory("WhmcsWhois");
            }
        }
        return $provider;
    }
    public static function getDomainLookupProvider()
    {
        $providerName = \App\Helpers\Cfg::getValue("domainLookupProvider");
        if (is_null($providerName)) {
            $providerName = "WhmcsDomains";
            \App\Helpers\Cfg::setValue("domainLookupProvider", $providerName);
        }
        return $providerName;
    }
    public static function getDomainLookupRegistrar()
    {
        return \App\Helpers\Cfg::getValue("domainLookupRegistrar");
    }
    public static function getAvailableProviders()
    {
        return static::$providerNames;
    }
    public static function getAvailableRegistrarProviders()
    {
        $registrarModules = new \App\Module\Registrar();
        $registrars = $registrarModules->getList();
        $returnedRegistrars = array();
        foreach ($registrars as $registrar) {
            $registrarModules->load($registrar->getLowerName());
            if ($registrarModules->functionExists("CheckAvailability") || $registrarModules->functionExists("GetDomainSuggestions")) {
                $returnedRegistrars[$registrar->getLowerName()] = array(
                    "checks" => $registrarModules->functionExists("CheckAvailability"),
                    "suggestions" => $registrarModules->functionExists("GetDomainSuggestions"),
                    "logo" => $registrarModules->getLogoFilename(),
                    "name" => $registrarModules->getDisplayName(),
                    "suggestionSettings" => $registrarModules->functionExists("DomainSuggestionOptions") ? $registrarModules->call("DomainSuggestionOptions") : array(),
                );
            }
        }
        return $returnedRegistrars;
    }
}

?>

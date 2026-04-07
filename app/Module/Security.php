<?php

namespace App\Module;

class Security extends AbstractModule
{
    protected $type = self::TYPE_SECURITY;
    public function getActiveModules()
    {
        return (new \App\Helpers\TwoFactorAuthentication())->getAvailableModules();
    }
    public function getAdminActivationForms($moduleName)
    {
        // return array((new \WHMCS\View\Form())->setUriPrefixAdminBaseUrl("configtwofa.php")->setMethod(\WHMCS\View\Form::METHOD_GET)->setParameters(array("module" => $moduleName))->setSubmitLabel(\AdminLang::trans("global.activate")));
        return [];
    }
    public function getAdminManagementForms($moduleName)
    {
        // return array((new \WHMCS\View\Form())->setUriPrefixAdminBaseUrl("configtwofa.php")->setMethod(\WHMCS\View\Form::METHOD_GET)->setParameters(array("module" => $moduleName))->setSubmitLabel(\AdminLang::trans("global.manage")));
        return [];
    }
}

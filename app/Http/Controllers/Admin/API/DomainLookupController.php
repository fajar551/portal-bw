<?php

namespace App\Http\Controllers\Admin\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator, DB;
use App\Helpers\ResponseAPI;

class DomainLookupController extends Controller
{
    //
    public function index(Request $request)
    {
        $currency = \App\Helpers\Format::getCurrency();
        $action = $request->input("action");
        $response = "";

        if ($action == "save") {
            $response = array("status" => 0, "errorMsg" => "");
            DB::beginTransaction();
            try {
                $providerName = $request->input("domainLookupProvider");
                $registrarName = $request->input("domainLookupRegistrar");
                $provider = \App\Helpers\Domains\DomainLookup\Provider::factory($providerName, $registrarName);
                $configureStep = $request->input("configureStep");
                $existingProvider = \App\Helpers\Cfg::getValue("domainLookupProvider");
                if (!$registrarName) {
                    $registrarName = "WhmcsWhois";
                }
                if (!($providerSettings = $provider->getSettings())) {
                    throw new \App\Exceptions\Information(sprintf($aInt->lang("general", "domainLookupProviderHasNoSettings"), $providerName));
                }
                $userInputAllSettings = $request->input("providerSettings");
                if (isset($userInputAllSettings["WhmcsWhois"]["suggestTlds"]) && is_array($userInputAllSettings["WhmcsWhois"]["suggestTlds"])) {
                    $userInputAllSettings["WhmcsWhois"]["suggestTlds"] = implode(",", $userInputAllSettings["WhmcsWhois"]["suggestTlds"]);
                }
                if ($provider->getProviderName() == "WhmcsDomains") {
                    $userProvidedSettings = $userInputAllSettings["WhmcsWhois"];
                } else {
                    $settingKey = $providerName;
                    if ($providerName == "Registrar") {
                        $settingKey = $providerName . $registrarName;
                    }
                    $userProvidedSettings = $userInputAllSettings[$settingKey];
                }
                if (!is_array($userProvidedSettings)) {
                    throw new \App\Exceptions\Information(sprintf(\Lang::get('admin.generalinvalidSettingsForDomainLookupProvider'), $providerName));
                }
                \App\Helpers\Domains\DomainLookup\Settings::ofRegistrar($registrarName)->delete();
                foreach ($userProvidedSettings as $userProvidedSettingName => $userProvidedSettingValue) {
                    $setting = new \App\Helpers\Domains\DomainLookup\Settings();
                    $setting->registrar = $registrarName;
                    $setting->setting = $userProvidedSettingName;
                    $setting->value = $userProvidedSettingValue;
                    $setting->save();
                }
                if ($providerName == "WhmcsWhois") {
                    \App\Helpers\Cfg::set("PremiumDomains", 0);
                    \App\Helpers\Cfg::set("domainLookupProvider", "WhmcsWhois");
                    \App\Helpers\Cfg::set("domainLookupRegistrar", "");
                }
                if ($providerName == "Registrar") {
                    $loggedName = $provider->getRegistrar()->getDisplayName();
                } else {
                    if ($provider->getProviderName() == "WhmcsDomains") {
                        $loggedName = "WHMCS Namespinning";
                    } else {
                        $loggedName = "Standard Whois";
                    }
                }
                if ($providerName != $existingProvider) {
                    \App\Helpers\AdminFunctions::logAdminActivity("Domain Lookup Provider Activated: '" . $loggedName . "'");
                } else {
                    \App\Helpers\AdminFunctions::logAdminActivity("Domain Lookup Provider Settings Modified: '" . $loggedName . "'");
                }

                DB::commit();
                $response["status"] = 1;
                $response["statusMsg"] = \Lang::get("admin.success");
                $response["successMsg"] = \Lang::get("admin.changesuccess");
                $response["successMsgTitle"] = \Lang::get("admin.success");
                $response["dismiss"] = true;
            } catch (\Exception $e) {
                DB::rollBack();
                $response["status"] = 0;
                $response["errorMsg"] = \Lang::get("admin.generalcouldNotProcessRequest") . " " . $e->getMessage();
                $response["errorMsgTitle"] = \Lang::get("admin.error");
                \App\Helpers\LogActivity::Save("Error processing request: " . $e->getMessage());
            }
        }
        
        if ($action == "configure") {
            $providerName = "";
            $registrarName = "WhmcsWhois";
            try {
                $provider = \App\Helpers\Domains\DomainLookup\Provider::factory();
                $providerName = $provider->getProviderName();
                if ($providerName == "Registrar") {
                    $registrarName = $provider->getRegistrar()->getLoadedModule();
                }
                $providerSettings = $provider->getSettings();
                if (!$providerSettings) {
                    throw new \App\Exceptions\Information(sprintf(\Lang::get("admin.generaldomainLookupProviderHasNoSettings"), $providerName == "Registrar" ? $provider->getRegistrar()->getDisplayName() : $providerName));
                }
                $settings = \App\Helpers\Domains\DomainLookup\Settings::ofRegistrar($registrarName)->pluck("value", "setting");
                if ($provider instanceof \App\Helpers\Domains\DomainLookup\Provider\Registrar) {
                    $displayName = $provider->getRegistrar()->getDisplayName();
                    $fields = array();
                    foreach ($providerSettings as $name => $values) {
                        $values["Name"] = "providerSettings[Registrar" . $registrarName . "][" . $name . "]";
                        $values["Value"] = $settings[$name];
                        $fields[$values["FriendlyName"] ?: $name] = \App\Helpers\Module::moduleConfigFieldOutput($values);
                    }
                    $form = "
                    <div id=\"containerProviderSettingsEnom\">
                        <div style=\"padding:15px;text-align:center;\">
                            <img src=\"" . url('/') . "/modules/registrars/" . $registrarName . "/logo.gif\">
                        </div>
                        <div id=\"settingSaveStatusEnom\"></div>
                        <br/>
                        <form action=\"".route('apiconsumer.admin.setup.domainlookup.index')."\" method=\"POST\" name=\"providerSettings" . $displayName . "\" id=\"providerSettings" . $displayName . "\">" . csrf_field() . "
                            <input type=\"hidden\" name=\"domainLookupProvider\" value=\"" . $providerName . "\"/>
                            <input type=\"hidden\" name=\"domainLookupRegistrar\" value=\"" . $registrarName . "\"/>
                            <input type=\"hidden\" name=\"action\" value=\"save\" />
                            <div align=\"center\">";
                    foreach ($fields as $name => $output) {
                        $form .= (string) $name . "<br />" . $output . "<br /><br />";
                    }
                    $form = substr($form, 0, strlen($form) - 4);
                    $form .= "</div>
                        </form>
                    </div>";
                    $response = $form;
                } else {
                    if ($provider instanceof \App\Helpers\Domains\DomainLookup\Provider\WhmcsWhois) {
                        $suggestTlds = $providerSettings["suggestTlds"];
                        $settings["suggestTlds"] = json_encode(explode(",", @$settings["suggestTlds"]));
                        
                        $fields = array();
                        $providerName = $provider->getProviderName();
                        foreach ($providerSettings as $name => $values) {
                            $values["Name"] = "providerSettings[WhmcsWhois][" . $name . "]";
                            $values["Value"] = @$settings[$name];
                            $fields[$values["FriendlyName"] ?: $name] = \App\Helpers\Module::moduleConfigFieldOutput($values);
                        }
                        // $imgPath = (new WHMCS\View\Asset(WHMCS\Utility\Environment\WebHelper::getBaseUrl(ROOTDIR, $_SERVER["SCRIPT_NAME"])))->getImgPath();
                        $imgPath = url('/');
                        if ($providerName == "WhmcsDomains") {
                            $img = $imgPath . "/lookup/whmcs-namespinning-large.png";
                        } else {
                            $img = $imgPath . "/lookup/standard-whois.png";
                        }
                        $form = "
                        <div id=\"containerProviderSettingsWhmcsWhois\">
                            <div id=\"settingSaveStatusWhmcsWhois\"></div>
                        
                            <div style=\"padding:15px;text-align:center;\">
                                <img src=\"" . $img . "\"/>
                            </div>
                        
                            <form action=\"".route('apiconsumer.admin.setup.domainlookup.index')."\" method=\"POST\" name=\"providerSettingsWhmcsWhois\" id=\"providerSettingsWhmcsWhois\">" . csrf_field() . "
                                <input type=\"hidden\" name=\"domainLookupProvider\" value=\"" . $providerName . "\"/>
                                <input type=\"hidden\" name=\"action\" value=\"save\" />
                                <input type=\"hidden\" name=\"providerSettings[WhmcsWhois][useWhmcsWhoisForSuggestions]\" value=\"on\" />
                                <div align=\"center\">";
                        foreach ($fields as $name => $output) {
                            $form .= (string) $name . "<br />" . $output . "<br /><br />";
                        }
                        $form = substr($form, 0, strlen($form) - 4);
                        $form .= "</div>
                            </form>
                        </div>";
                        $response = $form;
                    } else {
                        throw new \Exception("Invalid Domain Lookup Provider '" . $providerName . "'");
                    }
                }
            } catch (\App\Exceptions\Information $e) {
                $response = "<div id=\"containerProviderSettings" . $providerName . "\" class=\"alert alert-info\" role=\"alert\">" . $e->getMessage() . "</div>";
            } catch (\Exception $e) {
                \App\Helpers\LogActivity::Save("Error processing request: " . $e->getMessage());
                $response = "<div id=\"containerProviderSettings" . $providerName . "\" class=\"alert alert-danger\" role=\"alert\">" . \Lang::get("admin.couldNotProcessRequest") . "</div>";
            }
        }

        return ResponseAPI::Success(is_array($response) ? $response : array("body" => $response));
    }
}

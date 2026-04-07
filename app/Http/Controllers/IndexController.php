<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class IndexController extends Controller
{
    //
    public function index(Request $request)
    {
        // @see vendor/whmcs/whmcs-foundation/lib/Module/ClientAreaController.php
        if ($moduleName = $request->get("m", "")) {
            $auth = Auth::guard('web')->user();

            $moduleName = preg_replace("/[^a-zA-Z0-9._]/", "", $moduleName);
            $addonModule = new \App\Module\Addons();
            if (!$addonModule->load($moduleName) || !$addonModule->functionExists("clientarea")) {
                return redirect()->route('home');
            }

            $configarray = $addonModule->call("config");
            $modulevars = array();
            $result = \App\Models\AddonModule::where(array("module" => $moduleName))->get();
            foreach ($result->toArray() as $data) {
                $modulevars[$data["setting"]] = $data["value"];
            }
            if (!count($modulevars)) {
                return redirect()->route('home');
            }
            $modulevars["modulelink"] = "index.php?m=" . $moduleName;
            $_ADDONLANG = array();
            if (count($_ADDONLANG)) {
                $modulevars["_lang"] = $_ADDONLANG;
            }
            $results = $addonModule->call("clientarea", $modulevars);
            if (!is_array($results)) {
                \App\Helpers\LogActivity::Save("Addon Module \"" . $moduleName . "\" returned an invalid client area output response type");
                return redirect()->route('home');
            }
            if (isset($results["requirelogin"]) && $results["requirelogin"] && !$auth) {
                $request->session()->put('url.intended', url($modulevars["modulelink"]));
                return view("auth.login");
            }
            $view = "";
            if (isset($results["templatefile"]) && $results["templatefile"] && ($templatePath = $addonModule->findTemplate($results["templatefile"]))) {
                $view = $templatePath;
            } else {
                \App\Helpers\LogActivity::Save("Addon Module \"" . $moduleName . "\" requested template file \"" . $results["templatefile"] . ".tpl\" which could not be found");
                return redirect()->route('home');
            }
            $templateVariables = array();
            if (isset($results["vars"]) && is_array($results["vars"])) {
                $templateVariables = $results["vars"];
            }
            if (isset($results["templateVariables"]) && is_array($results["templateVariables"])) {
                $templateVariables = array_merge($templateVariables, $results["templateVariables"]);
            }
            // hooks
            $hookResponses = \App\Helpers\Hooks::run_hook("ClientAreaPageAddonModule", $templateVariables);
            foreach ($hookResponses as $hookTemplateVariables) {
                foreach ($hookTemplateVariables as $k => $v) {
                    $templateVariables[$k] = $v;
                }
            }
            return view($view, $templateVariables);
        } else {
            if (Auth::guard('web')->check()) {
                return redirect()->route('home');
            } else {
                return view("auth.login");
            }
        }
    }
}

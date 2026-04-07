<?php

namespace App\Http\Controllers\Admin\Setup\ProductConfig;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Nwidart\Modules\Facades\Module;

class DomainController extends Controller
{
    public function DomainPricing(Request $request)
    {
        $id = $request->get('id');
        
        $defaultCurrency = \App\Models\Currency::findOrFail(1);
        $extensions = \App\Models\Extension::all();
        $imgPath = url('/');
        $lookupProvider = \App\Helpers\Cfg::getValue("domainLookupProvider");
        $lookupRegistrar = \App\Helpers\Cfg::getValue("domainLookupRegistrar");
        $toggleDisabled = false;
        if ($lookupProvider == "WhmcsDomains") {
            $lookupRegistrar = "<img class=\"img-fluid mb-3\" src=\"" . $imgPath . "/lookup/whmcs-namespinning-large.png\">";
        } else {
            if ($lookupProvider == "Registrar") {
                if ($lookupRegistrar) {
                    $registrar = new \App\Module\Registrar();
                    $registrar->load($lookupRegistrar);
                    if ($lookupRegistrarLogo = $registrar->getLogoFilename()) {
                        $lookupRegistrar = "<img class=\"img-fluid mb-3\" id=\"imgLookupRegistrar\" src=\"" . $lookupRegistrarLogo . "\">";
                    }
                }
            } else {
                $toggleDisabled = true;
                $lookupRegistrar = "<img class=\"img-fluid mb-3\" src=\"" . $imgPath . "/lookup/standard-whois.png\">";
            }
        }
        $currencies = \App\Models\Currency::pluck("code", "id");
        $domainAddons = array();
        foreach ($currencies as $currencyId => $currencyCode) {
            $domainAddonPricing = \App\Models\Pricing::where("type", "=", "domainaddons")->where("relid", "=", 0)->where("currency", "=", $currencyId)->first();
            if (!$domainAddonPricing) {
                \App\Models\Pricing::insert(array("type" => "domainaddons", "currency" => $currencyId, "relid" => 0));
                $domainAddonPricing = \App\Models\Pricing::where("type", "=", "domainaddons")->where("relid", "=", 0)->where("currency", "=", $currencyId)->first(array("msetupfee", "qsetupfee", "ssetupfee"));
            }
            $domainAddons["dnsManagement"][$currencyId] = array("field" => "msetupfee", "price" => $domainAddonPricing->msetupfee);
            $domainAddons["emailForwarding"][$currencyId] = array("field" => "qsetupfee", "price" => $domainAddonPricing->qsetupfee);
            $domainAddons["idProtection"][$currencyId] = array("field" => "ssetupfee", "price" => $domainAddonPricing->ssetupfee);
        }
        $errorSwal = array("title" => \Lang::get("admin.error"), "text" => \Lang::get("admin.domainsmassUpdateError"), "confirmButtonText" => \Lang::get("admin.ok"));
        $massUpdateSwal = array("title" => \Lang::get("admin.areYouSure"), "text" => \Lang::get("admin.domainsmassUpdateConfirm"), "confirmButtonText" => \Lang::get("admin.yes"), "cancelButtonText" => \Lang::get("admin.no"));
    
        // edit pricing
        if ($id) {
            // post data
            $register = $request->input("register") ?? [];
            $transfer = $request->input("transfer") ?? [];
            $renew = $request->input("renew") ?? [];

            $cugrouparray = array();
            $clientGroup = NULL;
            if ($request->get("selectedcugroupid")) {
                $selectedcugroupid = $request->get("selectedcugroupid");
                $clientGroup = \App\Models\Clientgroup::find($selectedcugroupid);
            } else {
                $selectedcugroupid = 0;
            }
            $domainTld = \App\Models\Domainpricing::findOrFail($id);
            if ($register) {
                foreach ($register as $cugroupid => $register_values) {
                    foreach ($register_values as $curr_id => $values) {
                        \App\Models\Pricing::where(array("type" => "domainregister", "tsetupfee" => $selectedcugroupid, "currency" => $curr_id, "relid" => $id))->update(array("msetupfee" => $values[1], "qsetupfee" => $values[2], "ssetupfee" => $values[3], "asetupfee" => $values[4], "bsetupfee" => $values[5], "monthly" => $values[6], "quarterly" => $values[7], "semiannually" => $values[8], "annually" => $values[9], "biennially" => $values[10]));
                    }
                }
                foreach ($transfer as $cugroupid => $transfer_values) {
                    foreach ($transfer_values as $curr_id => $values) {
                        \App\Models\Pricing::where(array("type" => "domaintransfer", "tsetupfee" => $selectedcugroupid, "currency" => $curr_id, "relid" => $id))->update(array("msetupfee" => $values[1], "qsetupfee" => $values[2], "ssetupfee" => $values[3], "asetupfee" => $values[4], "bsetupfee" => $values[5], "monthly" => $values[6], "quarterly" => $values[7], "semiannually" => $values[8], "annually" => $values[9], "biennially" => $values[10]));
                    }
                }
                foreach ($renew as $cugroupid => $renew_values) {
                    foreach ($renew_values as $curr_id => $values) {
                        \App\Models\Pricing::where(array("type" => "domainrenew", "tsetupfee" => $selectedcugroupid, "currency" => $curr_id, "relid" => $id))->update(array("msetupfee" => $values[1], "qsetupfee" => $values[2], "ssetupfee" => $values[3], "asetupfee" => $values[4], "bsetupfee" => $values[5], "monthly" => $values[6], "quarterly" => $values[7], "semiannually" => $values[8], "annually" => $values[9], "biennially" => $values[10]));
                    }
                }
                if ($clientGroup) {
                    \App\Helpers\AdminFunctions::logAdminActivity("Domain Pricing Slab Modified: '" . $domainTld->extension . "' - '" . $clientGroup->groupname . "'");
                } else {
                    \App\Helpers\AdminFunctions::logAdminActivity("Domain Pricing Modified: '" . $domainTld->extension . "'");
                }
                \App\Helpers\Hooks::run_hook("TopLevelDomainPricingUpdate", array("tld" => $domainTld->extension));
                return redirect()->back()->with(['success' => 'Your changes have been saved.']);
            }
            $result = \App\Models\Currency::orderBy("code", "ASC")->get();
            $currenciesarray = [];
            foreach($result->toArray() as $data) {
                $curr_id = $data["id"];
                $curr_code = $data["code"];
                $currenciesarray[$curr_id] = $curr_code;
            }
            $pricing_id1a = 0;
            $pricing_id2a = 0;
            $pricing_id3a = 0;
            foreach ($currenciesarray as $curr_id => $curr_code) {
                $result2 = \App\Models\Pricing::where(array("type" => "domainregister", "tsetupfee" => $selectedcugroupid, "currency" => $curr_id, "relid" => $id));
                $data = $result2;
                $pricing_id1a = $data->value("id");
                if (!$pricing_id1a) {
                    $result2 = \App\Models\Pricing::where(array("type" => "domainregister", "tsetupfee" => "0", "currency" => $curr_id, "relid" => $id));
                    $data = $result2;
                    $pricing_id1b = $data->value("id");
                    if (!$pricing_id1b) {
                        $pricing_id1a = \App\Models\Pricing::insertGetId(array("type" => "domainregister", "currency" => $curr_id, "relid" => $id, "msetupfee" => "-1", "qsetupfee" => "-1", "ssetupfee" => "-1", "asetupfee" => "-1", "bsetupfee" => "-1", "monthly" => "-1", "quarterly" => "-1", "semiannually" => "-1", "annually" => "-1", "biennially" => "-1"));
                    }
                }
                $result2 = \App\Models\Pricing::where(array("type" => "domaintransfer", "tsetupfee" => $selectedcugroupid, "currency" => $curr_id, "relid" => $id));
                $data = $result2;
                $pricing_id2a = $data->value("id");
                if (!$pricing_id2a) {
                    $result2 = \App\Models\Pricing::where(array("type" => "domaintransfer", "tsetupfee" => "0", "currency" => $curr_id, "relid" => $id));
                    $data = $result2;
                    $pricing_id2b = $data->value("id");
                    if (!$pricing_id2b) {
                        $pricing_id2a = \App\Models\Pricing::insertGetId(array("type" => "domaintransfer", "currency" => $curr_id, "relid" => $id));
                    }
                }
                $result2 = \App\Models\Pricing::where(array("type" => "domainrenew", "tsetupfee" => $selectedcugroupid, "currency" => $curr_id, "relid" => $id));
                $data = $result2;
                $pricing_id3a = $data->value("id");
                if (!$pricing_id3a) {
                    $result2 = \App\Models\Pricing::where(array("type" => "domainrenew", "tsetupfee" => "0", "currency" => $curr_id, "relid" => $id));
                    $data = $result2;
                    $pricing_id3b = $data->value("id");
                    if (!$pricing_id3b) {
                        $pricing_id3a = \App\Models\Pricing::insertGetId(array("type" => "domainrenew", "currency" => $curr_id, "relid" => $id));
                    }
                }
            }
            $noslabpricing = !$pricing_id1a || !$pricing_id2a || !$pricing_id3a ? true : false;

            return view('pages.setup.prodsservices.domainpricing.edit', [
                'id' => $id,
                'extension' => $domainTld->extension,
                'currenciesarray' => $currenciesarray,
                'selectedcugroupid' => $selectedcugroupid,
                'noslabpricing' => $noslabpricing,
                'totalcurrencies' => count($currenciesarray),
                'register' => $register,
                'transfer' => $transfer,
                'renew' => $renew,
            ]);
        }

        return view('pages.setup.prodsservices.domainpricing.index', [
            'defaultCurrency' => $defaultCurrency,
            'extensions' => $extensions,
            'lookupProvider' => $lookupProvider,
            'lookupRegistrar' => $lookupRegistrar,
            'domainAddons' => $domainAddons,
            'currencies' => $currencies,
            'errorSwal' => $errorSwal,
            'massUpdateSwal' => $massUpdateSwal,
        ]);
    }
    public function DomainRegistrars()
    {
        $data = [];
        $registrar = new \App\Module\Registrar();
        $modulesarray = $registrar->getList();
        foreach ($modulesarray as $module) {
            $moduleactive = false;
            $registrar->load($module->getLowerName());
            $moduleconfigdata = $registrar->getSettings();
            if (is_array($moduleconfigdata) && !empty($moduleconfigdata)) {
                $moduleactive = true;
            }
            $configarray = $registrar->call("getConfigArray");
            $displayName = $registrar->getDisplayName();
            $data[] = [
                'module' => $module,
                'moduleactive' => $moduleactive,
                'moduleconfigdata' => $moduleconfigdata,
                'displayName' => $displayName,
                'configarray' => $configarray,
                'logo' => Module::asset($module->getLowerName().':logo.gif'),
                // 'logo' => asset('logo.png'),
            ];
        }
        // dd($data);

        return view('pages.setup.prodsservices.domainregistrars.index', [
            'registrars' => $data,
        ]);
    }
    public function DomainRegistrars_active(Request $request)
    {
        $module = $request->input('module');
        $registrar = new \App\Module\Registrar();
        if ($registrar->load($module)) {
            $registrar->activate();
            return redirect()->back()->with(['success' => "The selected registrar was activated successfully."]);
        } else {
            return redirect()->back()->with(['error' => "Module not found"]);
        }
    }
    public function DomainRegistrars_deactive(Request $request)
    {
        $module = $request->input('module');
        $registrar = new \App\Module\Registrar();
        if ($registrar->load($module)) {
            \App\Helpers\AdminFunctions::logAdminActivity("Registrar Deactivated: '" . $registrar->getDisplayName() . "'");
            $registrar->deactivate();
            return redirect()->back()->with(['success' => "The selected registrar was deactivated successfully."]);
        } else {
            return redirect()->back()->with(['error' => "Module not found"]);
        }
    }
}

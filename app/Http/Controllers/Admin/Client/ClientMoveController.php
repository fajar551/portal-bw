<?php

namespace App\Http\Controllers\Admin\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Validator;

// Helpers
use App\Helpers\AdminFunctions;
use App\Helpers\Domains as HelpersDomain;
use App\Helpers\LogActivity;

// Models
use App\Models\Domain;
use App\Models\Hosting;
use App\Models\Hostingaddon;
use App\Models\Sslorder;

// Module
use App\Module\Server;

// Traits
use App\Traits\DatatableFilter;

class ClientMoveController extends Controller
{
    use DatatableFilter;

    public function __construct() {
        $this->middleware(['auth:admin']);
        $this->prefix = \Database::prefix();
    }

    public function index(Request $request)
    {   
        $id = $request->id;
        $type = $request->type;
        
        $templatevars["id"] = $id;
        $templatevars["type"] = $type;
        $templatevars["newuserid"] = $request->newuserid;
        $templatevars["submited"] = $request->submited;

        if ($type == "domain") {
            $domains = new HelpersDomain();
            $domainData = $domains->getDomainsDatabyID($id);
            $templatevars["domainData"] = $domainData;
        }
        
        return view('pages.clients.viewclients.clientmove.index', $templatevars);
    }

    public function transfer(Request $request)
    {
        $id = $request->id;
        $type = $request->type;
        $qparams = ['type' => $type, 'id' => $id];

        $validator = Validator::make($request->all(), [
            'newuserid' => "required|integer|exists:App\Models\Client,id",
            'type' => "required|string|in:domain,hosting",
            'id' => "required|integer|" .($type == 'domain' ? "exists:App\Models\Domain,id" : "exists:App\Models\Hosting,id"),
        ]);

        if ($validator->fails()) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clientmove.index", $qparams)
                    ->withErrors($validator)
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', AdminFunctions::infoBoxMessage('<b>Oh No!</b>', 'Please ensure to fill all fields correctly and re-submit the form.'));
        }

        switch ($type) {
            case 'domain':
                return $this->moveDomain($request);
            case 'hosting':
                return $this->moveHosting($request);
            default:
                # code...
                break;
        }

        return abort(404, "Ups... Action not found!");
    }

    private function moveDomain(Request $request)
    {
        $id = $request->id;
        $newuserid = trim($request->newuserid);
    
        $data = Domain::select("userid")->find($id);        
        $userid = $data->userid;
    
        Domain::where("id", $id)->update(["userid" => $newuserid]);
        LogActivity::Save("Moved Domain ID: $id from User ID: $userid to User ID: $newuserid", $newuserid);
    
        $qparams = ['type' => 'domain', 'id' => $id, 'submited' => true, 'newuserid' => $newuserid];
        return redirect()
                ->route("admin.pages.clients.viewclients.clientmove.index", $qparams)
                ->with('type', 'success')
                ->with('message', AdminFunctions::infoBoxMessage("admin.success", __("admin.domainstransuccess"))); 
    }
    
    private function moveHosting(Request $request)
    {
        $id = $request->id;
        $newuserid = trim($request->newuserid);

        $data = Hosting::select("userid")->find($id);
        $userid = $data->userid;
        $moduleInterface = "";
        $hasAppLinks = false;

        try {
            $moduleInterface = new Server();
            if ($moduleInterface->loadByServiceID($id) && $moduleInterface->isApplicationLinkSupported() && $moduleInterface->isApplicationLinkingEnabled()) {
                $call = "Delete";
                $moduleInterface->doSingleApplicationLinkCall($call);
                $hasAppLinks = true;
            }
        } catch (\Exception $e) {
            # Code
        }

        LogActivity::Save("Moved Service ID: $id from User ID: $userid to User ID: $newuserid", $newuserid);
        Hosting::where("id", $id)->update(["userid" => $newuserid]);
        $addons = Hostingaddon::where("hostingid", $id)->get();
        $addonsWithAppLinks = [];
        $addonModuleInterface = "";
        $hasAddonAppLinks = false;

        foreach ($addons as $addon) {
            try {
                $addonModuleInterface = new Server();
                if ($addonModuleInterface->loadByAddonId($addon->id) && $addonModuleInterface->isApplicationLinkSupported() && $addonModuleInterface->isApplicationLinkingEnabled()) {
                    $addonsWithAppLinks[] = $addon->id;
                    $call = "Delete";
                    $addonModuleInterface->doSingleApplicationLinkCall($call);
                    $hasAddonAppLinks = true;
                }
            } catch (\Exception $e) {
                # Code
            }
        }

        Hostingaddon::where("hostingid", $id)->update(["userid" => $newuserid]);
        Sslorder::where("serviceid", $id)->update(["userid" => $newuserid]);
            
        if ($hasAppLinks == true) {
            try {
                $moduleInterface = new Server();
                if ($moduleInterface->loadByServiceID($id) && $moduleInterface->isApplicationLinkSupported() && $moduleInterface->isApplicationLinkingEnabled()) {
                    $call = "Create";
                    $moduleInterface->doSingleApplicationLinkCall($call);
                }
            } catch (\Exception $e) {
                # Code
            }
        }

        if ($hasAddonAppLinks) {
            foreach ($addonsWithAppLinks as $addonId) {
                try {
                    $addonModuleInterface = new Server();
                    if ($addonModuleInterface->loadByAddonId($addonId) && $addonModuleInterface->isApplicationLinkSupported() && $addonModuleInterface->isApplicationLinkingEnabled()) {
                        $call = "Create";
                        $addonModuleInterface->doSingleApplicationLinkCall($call);
                    }
                } catch (\Exception $e) {
                    # Code
                }
            }
        }

        $qparams = ['type' => 'hosting', 'id' => $id, 'submited' => true, 'newuserid' => $newuserid];
        return redirect()
                ->route("admin.pages.clients.viewclients.clientmove.index", $qparams)
                ->with('type', 'success')
                ->with('message', AdminFunctions::infoBoxMessage("admin.success", __("admin.domainstransuccess"))); 
    }

}

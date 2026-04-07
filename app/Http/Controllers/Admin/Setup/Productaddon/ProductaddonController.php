<?php

namespace App\Http\Controllers\Admin\Setup\Productaddon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Cfg;
use App\Helpers\LogActivity;
use Carbon\Carbon;
use Validator;
use DataTables;
use Illuminate\Support\Facades\DB;
use App\Helpers\Database;

class ProductaddonController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
        $this->prefix=\Database::prefix();
        $this->adminURL =request()->segment(1).'/';
    }


    public function index(){


        return view('pages.setup.prodsservices.productaddons.index');
    }


    public function ProductaddonsData(Request $request){

        $Addon=\App\Models\Addon::select('id','packages','name','description','billingcycle','showorder','hidden','weight');

        return Datatables::of($Addon)
                            ->addColumn('delete', function($data) {
                                return (bool) (int) \App\Models\Hostingaddon::where('addonid',$data->id)->count();
                            })
                            ->rawColumns(['delete'])
                            ->toJson();
    }

    public function ProductAddons_add(){

        $currencies=\App\Models\Currency::select('id','code')->orderBy('code')->get();
        $cycles = ["monthly", "quarterly", "semiannually", "annually", "biennially", "triennially"];
        $legacyCycles = [
                                "One Time"      => [
                                                        "setup" => "msetupfee",
                                                        "term" => "monthly"
                                                    ],
                                "Monthly"       => [
                                                    "setup" => "msetupfee",
                                                    "term" => "monthly"
                                                ],
                                "Quarterly"     => [
                                                        "setup" => "qsetupfee",
                                                        "term" => "quarterly"
                                                    ],
                                "Semi-Annually" => [
                                                        "setup" => "ssetupfee",
                                                        "term" => "semiannually"
                                                    ],
                                "Annually"      => [
                                                    "setup" => "asetupfee",
                                                    "term" => "annually"
                                                ],
                                "Biennially"    => [
                                                    "setup" => "bsetupfee",
                                                    "term" => "biennially"
                                                ],
                                "Triennially"   => [
                                                    "setup" => "tsetupfee",
                                                    "term" => "triennially"
                                                ]
                        ];
       // dd($currencies);

       /*  $onetime=array();
        $id=0;
        foreach($currencies as $r){
            $currency_id = $r->id;
            $currency_code = $r->code;
            $pricing=\App\Models\Pricing::where("type", "=", "addon")->where("currency", "=", $currency_id)->where("relid", "=", 0)->first();
            if (is_null($pricing)){
                $addonData = array("type" => "addon", "currency" => $currency_id, "relid" => $id);
                foreach ($cycles as $cycle) {
                    $addonData[$cycle] = "-1";
                }
                $pricingId  = DB::table("tblpricing")->insertGetId($addonData);
                $pricing    = DB::table("tblpricing")->find($pricingId);
                if (!$id) {
                    DB::table("tblpricing")->where("type", "=", "addon")->where("relid", "=", 0)->delete();
                }
            }
            $legacyPricingStorage = false;




            //$onetime[]=
        } */
        // /dd($currencies);
        $product=\App\Helpers\Product::getProducts();
        //dd($product);
        $emailtemplate=\App\Models\Emailtemplate::where("type", "=", "product")->where("language", "=", "")->select('id','name')->orderBy("name")->get();
        $serverGroups = DB::table($this->prefix."servergroups as tblservergroups")
                        ->join($this->prefix."servergroupsrel as tblservergroupsrel", "tblservergroups.id", "=", "tblservergroupsrel.groupid")
                        ->join($this->prefix."servers as tblservers", "tblservergroupsrel.serverid", "=", "tblservers.id")
                        ->groupBy("tblservergroups.id")
                        ->selectRaw("tblservergroups.id,tblservergroups.name,CONCAT(\",\", GROUP_CONCAT(DISTINCT tblservers.type SEPARATOR \",\"), \",\") as server_types")
                        ->get();
        /* dd($serverGroups); */

        $server = new \App\Module\Server();
        $serverModules = $server->getListWithDisplayNames();

        $param=[
                'emailtemplate'     => $emailtemplate,
                'onetime'           => $currencies,
                'cycles'            => $cycles,
                'currencies'        => $currencies,
                'product'           => $product,
                'server'            => $serverGroups,
                'serverModules' => $serverModules,
            ];
        return view('pages.setup.prodsservices.productaddons.add',$param);
    }

    public function ProductAddons_store(Request $request){




        $createdNew = false;
        $name           = $request->name ?? '';
        $description    = $request->description ?? '';
        $billingCycle   = $request->billingcycle ?? 'free';
        $packages       = $request->packages ??array();
        $tax            = (bool) (int)  $request->tax ?? 0;
        $showOrder      = (bool) (int)  $request->showorder;
        $hide           = (bool) (int)  $request->hidden;
        $retired        = (bool) (int)  $request->retired;
        $autoActivate   = $request->autoactivate ?? '';
        $suspendProduct = (bool) (int)  $request->suspendproduct;
        $downloads      =  $request->downloads ?: array();
        $welcomeEmail   = (int)  $request->welcomeemail;
        $weight         = (int)  $request->weight ?? 0 ;
        $module         =  $request->servertype ?? '' ;
        $serverGroup    = (int)  $request->servergroup;
        $type           =  $request->type;
        $changedRecurring       = false;
        $hasServerTypeChanged   = false;
        $oldServerModule        = "";

        //dd($request->all());
        //if($billingCycle != 'free'){
            if($request->billingcycle == 'onetime'){
                $request->currency =  $request->currency['onetime'];
            }else{
                $request->currency =  $request->currency['recurring'];
            }
       //}else{
            //$request->currency =array();
       // }
        //$currencies=\App\Models\Currency::select('id','code')->orderBy('code')->get();
       // dd($currencies);
        //dd($request->all());

        $addon = new \App\Models\Addon();
        $addon->name = $name;
        $addon->description = \App\Helpers\Sanitize::decode($description);
        $addon->billingCycle = $billingCycle;
        $addon->packages = $packages;
        $addon->tax = $tax;
        $addon->showorder = $showOrder;
        $addon->hidden = $hide;
        $addon->retired = $retired;
        $addon->autoactivate = $autoActivate;
        $addon->suspendproduct = $suspendProduct;
        $addon->downloads = $downloads;
        $addon->welcomeemail = $welcomeEmail;
        $addon->weight = $weight;
        $addon->module = $module;
        $addon->type = $type;
        $addon->server_group_id = $serverGroup;
        $addon->save();
        $id = $addon->id;

        LogActivity::Save("Product Addon Created: ".$name." - Product Addon ID: ".$id);
        $createdNew = true;
        //pricing



        foreach ($request->currency as $currency_id => $pricing){
            //dd($pricing);
            DB::table($this->prefix."pricing")->insert(array_merge($pricing, array("type" => "addon", "currency" => $currency_id, "relid" => $id)));
        }

        //customflied
        //dd($request->all());
        $fieldChanges = array();
        if($request->addFieldName){
            $addFieldName           = $request->addFieldName ?? '';
            $addFieldType           = $request->addFieldType ?? '';
            $addFieldDescription    = $request->addFieldDescription ?? '';
            //$addFieldOptions        = explode(",", $request->addFieldOptions);
            $addFieldOptions        = $request->addFieldOptions??'';
            $addFieldExpression     =  \App\Helpers\Sanitize::decode($request->addFieldExpression);
            $addFieldAdmin          = $request->addFieldAdmin ?? '';
            $addFieldRequired       = $request->addFieldRequired ?? '';
            $addFieldShowOrder      = $request->addSortOrder;
            $addFieldShowInvoice    = $request->addFieldShowInvoice ?? '';
            $addFieldSortOrder      = $request->addSortOrder ?? 0;
            $fieldChanges[]         = "Custom Field Created: '" . $addFieldName . "'";

            $customField            = new \App\Models\Customfield();
            $customField->type      = "addon";
            $customField->relatedId = $id;
            $customField->fieldname = $addFieldName;
            $customField->fieldtype = $addFieldType;
            $customField->description       = $addFieldDescription;
            $customField->fieldoptions      = $addFieldOptions;
            $customField->regexpr           = $addFieldExpression;
            $customField->adminonly         = $addFieldAdmin;
            $customField->required          = $addFieldRequired;
            $customField->showorder         = "";
            $customField->showinvoice       = $addFieldShowInvoice;
            $customField->sortorder         = $addFieldSortOrder;
            $customField->save();
            //dd($customField);
        }

        //load config server
        $server= new \App\Module\Server();
        $newServer = $server->load($module);
        if ($hasServerTypeChanged) {
            $oldServer = new \App\Module\Server();
            $oldName = $oldServer->load($oldServerModule) ? $oldServer->getDisplayName() : "";
            $newName = $newServer ? $server->getDisplayName() : "";
            $fieldChanges[] = "Server Module Modified: '" . $oldName . "' to '" . $newName . "'";
            \App\Models\ModuleConfiguration::where("entity_type", "=", "addon")->where("entity_id", "=", $id)->delete();
        }
        $packageConfigOptions = $request->input("packageconfigoption") ?: array();
        if ($server->functionExists("ConfigOptions")) {
            $configArray = $server->call("ConfigOptions", array("producttype" => $addon->type, "addon" => true));
            $counter = 0;
            foreach ($configArray as $key => $values) {
                $friendlyName = $key;
                if (array_key_exists("FriendlyName", $values)) {
                    $friendlyName = $values["FriendlyName"];
                }
                $counter++;
                $field = "configoption" . $counter;
                if (!isset($packageconfigoption[$counter])) {
                    $moduleConfiguration = $addon->moduleConfiguration->where("setting_name", $field)->first();
                    $packageConfigOptions[$counter] = $moduleConfiguration ? $moduleConfiguration->value : "";
                    if ($hasServerTypeChanged) {
                        $packageConfigOptions[$counter] = "";
                    }
                }
                $saveValue = is_array($packageConfigOptions[$counter]) ? $packageConfigOptions[$counter] : trim($packageConfigOptions[$counter]);
                if (!$hasServerTypeChanged) {
                    $existingValue = $addon->moduleConfiguration->where("setting_name", $field)->first();
                    if ($existingValue) {
                        $existingValue = $existingValue->value;
                    }
                    if ($values["Type"] == "password") {
                        $updatedPassword = \App\Helpers\AdminFunctions::interpretMaskedPasswordChangeForStorage($saveValue, $existingValue);
                        if ($updatedPassword === false) {
                            continue;
                        }
                        if ($updatedPassword) {
                            $fieldChanges[] = (string) $key . " Value Modified";
                        }
                    } else {
                        if (is_array($saveValue)) {
                            $saveValue = json_encode($saveValue);
                            if ($saveValue != $existingValue) {
                                $fieldChanges[] = (string) $key . " Value Modified";
                            }
                        } else {
                            $saveValue = \App\Helpers\Sanitize::decode($saveValue);
                            if ($saveValue != $existingValue) {
                                $fieldChanges[] = (string) $key . " Value Modified: '" . $existingValue . "' to '" . $saveValue . "'";
                            }
                        }
                    }
                } else {
                    if (is_array($saveValue)) {
                        $saveValue = json_encode($saveValue);
                    } else {
                        $saveValue = \App\Helpers\Sanitize::decode($saveValue);
                    }
                }
                $moduleConfiguration = $addon->moduleConfiguration->where("setting_name", $field)->first();
                if (!$moduleConfiguration) {
                    $moduleConfiguration = new \App\Models\ModuleConfiguration();
                }
                $moduleConfiguration->entityType = "addon";
                $moduleConfiguration->entityId = $id;
                $moduleConfiguration->friendlyName = $friendlyName;
                $moduleConfiguration->settingName = $field;
                $moduleConfiguration->value = $saveValue;
                $moduleConfiguration->save();
            }
        }
        if ($fieldChanges) {
            $logStart = "Product Addon Modified";
            if ($createdNew) {
                $logStart = "Product Addon Creatd";
            }
            \App\Helpers\AdminFunctions::logAdminActivity((string) $logStart . " '" . $name . "' - " . implode(". ", $fieldChanges) . " - Product Addon ID: " . $id);
        }
        \App\Helpers\Hooks::run_hook("AddonConfigSave", array("id" => $id));

        // redirect here
        // sementara doang nanti ubah langsung ke halaman edit addon
        return redirect( $this->adminURL.'setup/productservices/productaddons/edit/'.$id)->with(['success' => 'Saved']);
    }

    private function saveModuleSettings(Request $request, $relid)
    {
        $module = $request->input("servertype");

        $server = new WHMCS\Module\Server();
        $newServer = $server->load($module);
    }

    public function ProductDestroy(Request $request){
        //dd($request->all());
        // /checkPermission("Delete Products/Services");
        $id=(int) $request->id;
        if(\App\Models\Hostingaddon::where('addonid',$id)->count()){
            return back()->withErrors(["'You cannot delete a product addon that is in use. To delete the addon, you need to first re-assign or remove the service addons using it."]);
        }
        \App\Helpers\Hooks::run_hook("ProductAddonDelete",['id' => $id]);

        $addons=\App\Models\Addon::find($id);
        $name=$addons->name;

        //ModuleConfiguration
        \App\Models\ModuleConfiguration::where('entity_id',$id)->delete();
        \App\Models\Pricing::where('type','addon')->where('relid',$id)->delete();
        $addons->delete();
        LogActivity::Save("Product Addon Deleted: ".$name." - Product Addon ID: ".$id);
        return back()->with('success', 'Successfully Delete product addon');
    }

    public function ProductAddons_edit($id){
        $id=(int)$id;



        //dd($addons);
        $currencies=\App\Models\Currency::select('id','code')->orderBy('code')->get();

        $cycles = ["monthly", "quarterly", "semiannually", "annually", "biennially", "triennially"];
        $legacyCycles = [
                                "One Time"      => [
                                                        "setup" => "msetupfee",
                                                        "term" => "monthly"
                                                    ],
                                "Monthly"       => [
                                                    "setup" => "msetupfee",
                                                    "term" => "monthly"
                                                ],
                                "Quarterly"     => [
                                                        "setup" => "qsetupfee",
                                                        "term" => "quarterly"
                                                    ],
                                "Semi-Annually" => [
                                                        "setup" => "ssetupfee",
                                                        "term" => "semiannually"
                                                    ],
                                "Annually"      => [
                                                    "setup" => "asetupfee",
                                                    "term" => "annually"
                                                ],
                                "Biennially"    => [
                                                    "setup" => "bsetupfee",
                                                    "term" => "biennially"
                                                ],
                                "Triennially"   => [
                                                    "setup" => "tsetupfee",
                                                    "term" => "triennially"
                                                ]
                        ];

        $product=\App\Helpers\Product::getProducts();

        $emailtemplate=\App\Models\Emailtemplate::where("type", "=", "product")->where("language", "=", "")->select('id','name')->orderBy("name")->get();
        $serverGroups = DB::table($this->prefix."servergroups as tblservergroups")
                        ->join($this->prefix."servergroupsrel as tblservergroupsrel", "tblservergroups.id", "=", "tblservergroupsrel.groupid")
                        ->join($this->prefix."servers as tblservers", "tblservergroupsrel.serverid", "=", "tblservers.id")
                        ->groupBy("tblservergroups.id")
                        ->selectRaw("tblservergroups.id,tblservergroups.name,CONCAT(\",\", GROUP_CONCAT(DISTINCT tblservers.type SEPARATOR \",\"), \",\") as server_types")
                        ->get();
        /* dd($serverGroups); */

        $server = new \App\Module\Server();
        $serverModules = $server->getListWithDisplayNames();
        /*Master */
        $addons=\App\Models\Addon::find($id);
        //dd($addons);
        $price=array();
        $price['onetime']=array();
        $price['recurring']=array();
        foreach($currencies as $r){
            $price[$addons->billingcycle][$r->id]= \App\Models\Pricing::where('type','addon')->where('relid',$id)->where('currency',$r->id)->first();
        }

        if($addons->billingcycle == 'onetime'){
            if(empty($price['recurring'])){
                $price['recurring'] =$price['onetime'];
            }
        }
        if($addons->billingcycle != 'onetime'){
            if(empty($price['onetime'])){
                $price['onetime'] =$price['recurring'];
            }
        }

        //dd($price);
        /*master end */
        $customField=\App\Models\Customfield::where('type','addon')->where('relid',$id)->orderBy('sortorder')->get();
        //dd($customField);

        $setup=[
                    'msetupfee',
                    'qsetupfee',
                    'ssetupfee',
                    'asetupfee',
                    'bsetupfee',
                    'tsetupfee'
                ];

        $param=[
                'data'              => $addons,
                'price'             => $price,
                'emailtemplate'     => $emailtemplate,
                'onetime'           => $currencies,
                'cycles'            => $cycles,
                'currencies'        => $currencies,
                'product'           => $product,
                'server'            => $serverGroups,
                'serverModules'     => $serverModules,
                'customField'       => $customField,
                'setup'             => $setup
            ];
            //dd($param);
        return view('pages.setup.prodsservices.productaddons.edit',$param);


    }

    public function ProductAddons_update(Request $request){
       /*  $server= new \App\Module\Server();
        $newServer = $server->load('Cpanel');
        dd($newServer); */
        //dd($request->all());


        $createdNew = false;
        $pricingEditDisabled = false;
        $id             = (int)$request->id;
        $name           = $request->name ?? '';
        $description    = $request->description ?? '';
        $billingCycle   = $request->billingcycle ?? 'free';
        $packages       = $request->packages ??array();
        $tax            = (bool) (int)  $request->tax;
        $showOrder      = (bool) (int)  $request->showorder;
        $addSortOrder   = (bool) (int)  $request->addSortOrder ?? 0;
        $hide           = (bool) (int)  $request->hidden;
        $retired        = (bool) (int)  $request->retired;
        $autoActivate   = $request->autoactivate ?? '';
        $suspendProduct = (bool) (int)  $request->suspendproduct;
        $downloads      =  $request->downloads ?: array();
        $welcomeEmail   = (int)  $request->welcomeemail;
        $weight         = (int)  $request->weight;
        $module         =  $request->servertype ?? '';
        $serverGroup    = (int)  $request->servergroup;
        $type           =  $request->type;
        $changedRecurring       = false;
        $hasServerTypeChanged   = false;
        $oldServerModule        = "";

        if($billingCycle != 'free'){

            if($request->billingcycle == 'onetime'){

                $currency =  $request->currency['onetime'];
            }else{

                $currency =  $request->currency['recurring'];
            }
        }else{
            $currency =array();
        }

        //dd($currency);

        $addon=\App\Models\Addon::find($id);
        if($addon->name !=  $name ){
            LogActivity::Save("Product Addon Modified: Name Changed: '" . $addon->name . "' to '" . $name . "' - Product Addon ID: " . $id);
            $addon->name = $name;
        }
        if ($addon->description != $description || $addon->billingcycle != $billingCycle || $addon->packages != $packages || $addon->tax != $tax || $addon->showorder != $showOrder || $addon->hidden != $hide || $addon->retired != $retired || $addon->autoactivate != $autoActivate || $addon->suspendproduct != $suspendProduct || $addon->downloads != $downloads || $addon->welcomeemail != $welcomeEmail || $addon->weight != $weight || $addon->module != $module || $addon->type != $type || $addon->server_group_id != $serverGroup) {
            LogActivity::Save("Product Addon Modified: '" . $name . "' - Product Addon ID: " . $id);
            if ($billingCycle == "recurring" && $addon->billingcycle != $billingCycle || $addon->billingcycle == "recurring" && $billingCycle != $addon->billingcycle) {
                $changedRecurring = true;
            }
            $addon->description =  \App\Helpers\Sanitize::decode($description);
            $addon->billingcycle = $billingCycle;
            $addon->packages = $packages;
            $addon->tax = $tax;
            $addon->showorder = $showOrder;
            $addon->hidden = $hide;
            $addon->retired = $retired;
            $addon->autoactivate = $autoActivate;
            $addon->suspendproduct = $suspendProduct;
            $addon->downloads = $downloads;
            $addon->welcomeemail = $welcomeEmail;
            $addon->weight = $weight;
            $addon->type = $type;
            $addon->server_group_id = $serverGroup;
            if ($addon->module != $module) {
                $oldServerModule = $addon->module;
                $hasServerTypeChanged = true;
                $addon->module = $module;
            }
        }
        $addon->save();

        $pricingUpdated = false;

        //currencyupdate
        if($currency){
            foreach($currency as $currency_id=>$pricing){
                $addonPricing = DB::table($this->prefix."pricing")->where("type", "=", "addon")->where("currency", "=", $currency_id)->where("relid", "=", $id)->first();

                foreach ($pricing as $keyName => $value) {
                    if ((@$addonPricing->{$keyName} != $value || $changedRecurring) && !$pricingUpdated) {
                        LogActivity::Save("Product Addon Modified: '" . $name . "' - Pricing Updated - Product Addon ID: " . $id);
                        $pricingUpdated = true;
                        break;
                    }
                }

                if ($billingCycle != "recurring") {
                    $pricing = array_merge($pricing, array("qsetupfee" => 0, "quarterly" => -1, "ssetupfee" => 0, "semiannually" => -1, "asetupfee" => 0, "annually" => -1, "bsetupfee" => 0, "biennially" => -1, "tsetupfee" => 0, "triennially" => -1));
                } else {
                    $cycleCount = 0;
                    $activeCycle = NULL;
                    $activeCycleTitleCase = NULL;
                    $cycles = array("monthly" => "Monthly", "quarterly" => "Quarterly", "semiannually" => "Semi-Annually", "annually" => "Annually", "biennially" => "Biennially", "triennially" => "Triennially");
                    foreach ($cycles as $cycle => $cycleTitleCase) {
                        if (0 <= $pricing[$cycle]) {
                            $activeCycle = $cycle;
                            $activeCycleTitleCase = $cycleTitleCase;
                            $cycleCount++;
                        }
                    }
                    //dd($pricing);
                    if ($cycleCount == 1) {
                        $setupfee = $pricing["msetupfee"];
                        $price = $pricing[$activeCycle];
                        foreach (array_keys($cycles) as $cycle) {
                            $pricing["msetupfee"] = 0;
                            $pricing[$cycle] = 0;
                        }
                        $pricing["msetupfee"] = $setupfee;
                        $pricing["monthly"] = $price;
                        //$addon->billingCycle = $activeCycleTitleCase;
                        //$addon->save();
                    }


                }
                //dd($pricing);
                DB::table("tblpricing")->where("type", "=", "addon")->where("currency", "=", $currency_id)->where("relid", "=", $id)->update($pricing);
            }


        }
        $fieldChanges = array();
        //update custom flied
        $existingCustomflid=$request->old ?? array();
        if($existingCustomflid){
            foreach($existingCustomflid as $k => $v){
                $customID=(int)$k;
                $customField=\App\Models\Customfield::find($customID);
                if ($customField->fieldname != $v['addFieldName']){
                    $fieldChanges[] = "Custom Field Name Modified: '" . $customField->fieldname . "' to '" . $v['addFieldName'] . "'";
                    $customField->fieldName = $v['addFieldName'] ??'' ;
                }

                if ($v['addFieldType'] != $customField->fieldtype || $v['addFieldDescription'] != $customField->description ||  @$v['addFieldOptions'] != $customField->fieldoptions || @$v['addFieldExpression'] != $customField->regexpr || @$v['addFieldAdmin'] != $customField->adminonly || @$v['addFieldRequired'] != $customField->required  || @$v['addFieldShowInvoice'] != $customField->showinvoice || $v['addSortOrder'] != $customField->showorder) {
                    $customField->fieldtype = $v['addFieldType']?? '';
                    $customField->description =  $v['addFieldDescription']?? '';
                    $customField->fieldoptions = $v['addFieldOptions'] ?? '';
                    $customField->regexpr = $v['addFieldExpression'] ?? '';
                    $customField->adminonly = $v['addFieldAdmin'] ??'';
                    $customField->required = $v['addFieldRequired'] ?? '';
                    $customField->showorder = $v['addFieldShowOrder'] ?? '';
                    $customField->showinvoice = $v['addFieldShowInvoice'] ?? '';
                    $customField->sortorder = $v['addSortOrder'] ?? 0;
                }

                $customField->save();
            }
        }

        //addcustomflid
        if($request->addFieldName){
            $addFieldName           = $request->addFieldName ?? '';
            $addFieldType           = $request->addFieldType ?? '';
            $addFieldDescription    = $request->addFieldDescription ?? '';
            //$addFieldOptions        = explode(",", $request->addFieldOptions);
            $addFieldOptions        = $request->addFieldOptions??'';
            $addFieldExpression     =  \App\Helpers\Sanitize::decode($request->addFieldExpression);
            $addFieldAdmin          = $request->addFieldAdmin ?? '';
            $addFieldRequired       = $request->addFieldRequired ?? '';
            $addFieldShowOrder      = $request->addSortOrder;
            $addFieldShowInvoice    = $request->addFieldShowInvoice ?? '';
            $addFieldSortOrder      = $request->addSortOrder ?? 0;
            $fieldChanges[]         = "Custom Field Created: '" . $addFieldName . "'";

            $customField            = new \App\Models\Customfield();
            $customField->type      = "addon";
            $customField->relatedId = $id;
            $customField->fieldname = $addFieldName;
            $customField->fieldtype = $addFieldType;
            $customField->description       = $addFieldDescription;
            $customField->fieldoptions      = $addFieldOptions;
            $customField->regexpr           = $addFieldExpression;
            $customField->adminonly         = $addFieldAdmin;
            $customField->required          = $addFieldRequired;
            $customField->showorder         = $request->addFieldShowOrder ?? '';
            $customField->showinvoice       = $addFieldShowInvoice;
            $customField->sortorder         = $addFieldSortOrder;
            $customField->save();
            //dd($customField);
        }

        if ($fieldChanges) {
            $logStart = "Product Addon Modified";
            LogActivity::Save((string) $logStart . " '" . $name . "' - " . implode(". ", $fieldChanges) . " - Product Addon ID: " . $id);
        }


        return back()->with('success', 'Product addons Successfully updated');
    }

    public function ProductDestroyCustomField(Request $request){
        $error=true;
        $alert='';
        //dd($request->all());
        $id=(int)$request->id;
        $customField =\App\Models\Customfield::find($id);

        if(is_null($customField)){
            $alert='not found';
        }else{
            $error=false;
            LogActivity::Save("Product Addon Modified: Custom Field Deleted: '" . $customField->name. "' - Addon ID: " . $id);
            $customField->delete();
        }

        $param=[
                    'error' => $error,
                    'alert' => $alert,
                    'id'    => $id
                ];
        echo  json_encode($param);

    }




}

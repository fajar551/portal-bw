<?php

namespace App\Http\Controllers\Admin\Setup\ProductConfig;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Nwidart\Modules\Facades\Module;

//Models
use App\Models\Productgroup;
use App\Models\Product;
use App\Models\ProductGroupFeature;
use App\Models\Pricing;
use App\Models\Productconfiggroup;
use App\Models\Productconfiglink;
use App\Models\Domainpricing;
use App\Models\ProductUpgradeProduct;
use App\Models\Emailtemplate;
//Helpers
use App\Helpers\Gateway;
use App\Helpers\ProductType;
use App\Helpers\Database;
use App\Helpers\Product as HelpersProduct;
use App\Helpers\LogActivity;

use API;
use App\Helpers\Cfg;
use App\Helpers\ResponseAPI;
use App\Models\Currency;
use Validator;
use DB;

class ProductServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
        $this->prefix = \Database::prefix();
    }

    public function ProductsServices(Request $request)
    {
        return view('pages.setup.prodsservices.productservices.index');
    }
    public function ProductsServices_dtProducts(Request $request)
    {
        $pfx = $this->prefix;
        $productgroups = DB::table("{$pfx}productgroups")
            ->leftJoin("{$pfx}products", "{$pfx}productgroups.id", "=", "{$pfx}products.gid")
            ->select(["{$pfx}productgroups.id", "{$pfx}productgroups.name", "{$pfx}products.name as prodsname", "{$pfx}products.id as pid", "{$pfx}products.type as prodtype", "{$pfx}products.paytype", "{$pfx}products.qty", "{$pfx}products.autosetup", "{$pfx}products.hidden as prodhidden"])->get();
        #
        return datatables()->of($productgroups)
            ->addColumn('group_name', function ($row) {
                $getGroups = Productgroup::where('id', $row->id)->get();
                $editGroupRoute = route('admin.pages.setup.prodsservices.productservices.editgroup', $row->id);
                foreach ($getGroups as $group) {
                    if (!$row->pid) {
                        $hidden = $group->hidden == 1 ? '(Hidden)' : '';
                        return "
                        <div class=\"d-inline\">
                            <a href=\"{$editGroupRoute}\" class=\"font-weight-bolder font-italic\">{$group->name} {$hidden}<a>
                        </div>
                        <div class=\"d-inline float-lg-right\">
                            <button type=\"button\" class=\"btn btn-danger btn-sm\" onclick=\"deleteGroup($row->id)\"><i class=\"fas fa-trash mr-2\"></i>Delete Group</button>
                        </div>
                        ";
                    } else {
                        return "
                        <div class=\"d-inline\">
                            <a href=\"{$editGroupRoute}\" class=\"font-weight-bolder\">{$group->name}</a>
                        </div>";
                    }
                }
            })->addColumn('id_group', function ($row) {
                return $row->id;
            })
            ->editColumn('name', function ($row) {
                if ($row->prodhidden == 1) {
                    return $row->prodsname . " (Hidden)";
                } else {
                    return $row->prodsname;
                }
            })
            ->editColumn('type', function ($row) {
                if ($row->pid) {
                    return ProductType::getName($row->prodtype);
                }
            })
            ->editColumn('paytype', function ($row) {
                return ucfirst($row->paytype);
            })
            ->editColumn('autosetup', function ($row) {
                if ($row->autosetup == '' && $row->pid) {
                    return 'Off';
                }
            })
            ->editColumn('actions', function ($row) {
                $action = "";

                $action .= "<button onclick=\"editProduct($row->pid)\" type=\"button\" class=\"btn btn-xs text-primary p-1 act-edit mr-3\" data-id=\"{$row->pid}\" title=\"Edit\"><i class=\"fa fa-edit\"></i></button> ";
                $action .= "<button onclick=\"ConfirmDelete($row->pid)\" type=\"button\" id=\"act-delete\" class=\"btn btn-xs text-danger p-1 \" data-id=\"{$row->pid}\" title=\"Delete\"><i class=\"fa fa-trash\"></i></button> ";
                if ($row->pid) {
                    return $action;
                }
            })
            ->rawColumns(['actions', 'group_name'])
            ->addIndexColumn()
            ->toJson();

        // $pfx = $this->prefix;
        // $query = Product::select(\DB::raw("{$pfx}products.*"));

        // return datatables()->of($query)->addColumn('gid', function ($row) {
        //     // $getGroupName = Productgroup::where('id', $row->gid)->pluck('name', 'id')->toArray();

        //     $getGroups = Productgroup::where('id', $row->gid)->get();
        //     // $getGroups = Productgroup::all();
        //     $editGroupRoute = route('admin.pages.setup.prodsservices.productservices.editgroup', $row->gid);
        //     foreach ($getGroups as $group) {
        //         if ($group->hidden == 1) {
        //             return "<a href=\"{$editGroupRoute}\" class=\"font-weight-bolder\">{$group->name} (Hidden)</a>";
        //         } else {
        //             return "<a href=\"{$editGroupRoute}\" class=\"font-weight-bolder\">{$group->name}</a>";
        //         }
        //     }
        // })
        //     ->addColumn('id_group', function ($row) {
        //         return $row->gid;
        //     })
        //     ->editColumn('name', function ($row) {
        //         if ($row->hidden == 1) {
        //             return $row->name . " (Hidden)";
        //         } else {
        //             return $row->name;
        //         }
        //     })
        //     ->editColumn('type', function ($row) {
        //         return ProductType::getName($row->type);
        //     })
        //     ->editColumn('paytype', function ($row) {
        //         return ucfirst($row->paytype);
        //     })
        //     ->editColumn('stockcontrol', function ($row) {
        //         if ($row->stockcontrol == 0) {
        //             return "-";
        //         }
        //         return $row->qty;
        //     })
        //     ->editColumn('autosetup', function ($row) {
        //         if ($row->autosetup == '') {
        //             return 'Off';
        //         }
        //     })
        //     ->editColumn('actions', function ($row) {
        //         $editRoute = route('admin.pages.setup.prodsservices.productservices.createproduct.edit', ['id' => $row->id]);
        //         $deleteRoute = route('admin.pages.setup.prodsservices.productservices.createproduct.delete', ['id' => $row->id]);
        //         $action = "";

        //         $action .= "<a href=\"{$editRoute}\" type=\"button\" class=\"btn btn-xs text-primary p-1 act-edit mr-3\" data-id=\"{$row->id}\" title=\"Edit\"><i class=\"fa fa-edit\"></i></a> ";
        //         $action .= "<button onclick=\"ConfirmDelete('{$deleteRoute}')\" type=\"button\" id=\"act-delete\" class=\"btn btn-xs text-danger p-1 \" data-id=\"{$row->id}\" title=\"Delete\"><i class=\"fa fa-trash\"></i></button> ";

        //         return $action;
        //     })
        //     ->orderColumn('id_group', function ($query, $order) {
        //         $query->orderBy('gid', $order);
        //     })
        //     ->rawColumns(['actions', 'gid'])
        //     ->addIndexColumn()
        //     ->toJson();
    }
    public function ProductsServices_creategroup()
    {
        $gateway = Gateway::GetGatewaysArray();
        $directory = base_path('themes\qwords');
        // dd($directory);
        $sub_directories = array_map('basename', glob($directory . '/*', GLOB_ONLYDIR));

        $getTemplateList = array_splice($sub_directories, 1, count($sub_directories));
        // dd($getTemplateList);
        return view('pages.setup.prodsservices.productservices.creategroup', [
            'gateway' => $gateway,
            'templateList' => $getTemplateList
        ]);
    }
    public function ProductsServices_creategroup_add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'headline' => 'nullable|string',
            'tagline' => 'nullable|string',
            'orderfrmtpl' => 'nullable|string',
            'disabledgateways[]' => 'nullable|string',
            'hidden' => 'nullable|numeric'
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.pages.setup.prodsservices.productservices.creategroup')->withErrors($validator)->with('message', 'Can\'t create new product groups , please fill your forms correctly and try again');
        }

        $prodGroup = new Productgroup();
        $prodGroup->name = $request->name;
        $prodGroup->headline = $request->headline;
        $prodGroup->tagline = $request->tagline;
        if ($request->orderfrmtpl) {
            $prodGroup->orderfrmtpl = $request->orderfrmtpl;
        } else {
            $prodGroup->orderfrmtpl = '';
        }
        if ($request->disabledgateways) {
            $disabledGate = $request->only('disabledgateways');
            foreach ($disabledGate as $gate) {
                $arrToStr = $gate;
            }
            $gateStr = implode(',', $arrToStr);
            $prodGroup->disabledgateways = $gateStr;
        } else {
            $prodGroup->disabledgateways = '';
        }
        $prodGroup->hidden = (int)$request->hidden;
        $prodGroup->order = Productgroup::count() + 1;

        $prodGroup->save();
        return redirect()->route('admin.pages.setup.prodsservices.productservices.creategroup')->with(['success' => 'A product group successfully created']);
    }
    public function ProductServices_editgroup($id)
    {
        $productFeature = ProductGroupFeature::where('product_group_id', $id)->pluck('feature')->toArray();
        $selectedGroup = Productgroup::findOrFail($id);
        $gateway = Gateway::GetGatewaysArray();
        $selectedGatewayStr = $selectedGroup->disabledgateways;
        $selectedGatewayArr = explode(",", $selectedGatewayStr);
        $asKeyGateways = array_fill_keys($selectedGatewayArr, 'done');

        $directory = base_path('themes\qwords'); //'D:\Qwords\CBMSAuto\cbms-auto-new\cbms-auto\themes\qwords'
        // dd($directory); 
        $sub_directories = array_map('basename', glob($directory . '/*', GLOB_ONLYDIR));
        $getTemplateList = array_splice($sub_directories, 1, count($sub_directories));

        $getOrderFormTemplate = API::post('GetConfigurationValue', ['setting' => 'OrderFormTemplate']);
        $getOrderFormTemplate = json_decode(json_encode($getOrderFormTemplate));

        // orderform themes
        $themesData = \App\Helpers\ThemeManager::all();
        $orderformThemes = collect($themesData)->where('vendor', \App\Helpers\ThemeManager::orderformTheme())->all();

        return view('pages.setup.prodsservices.productservices.editgroup', [
            'gateways' => $gateway,
            'selectedGroup' => $selectedGroup,
            'selectedGatewayArr' => $asKeyGateways,
            'productFeature' => $productFeature,
            'templateList' => $getTemplateList,
            'orderFormTemplate' => $selectedGroup->orderfrmtpl,
            'orderformThemes' => $orderformThemes,
        ]);
    }
    public function ProductService_deletegroup($id)
    {
        $productData = Productgroup::findOrFail($id);
        $productData->delete();
        return response()->json([
            'message' => 'OK',
            'text' => 'Product group has been deleted!'
        ]);
    }
    public function ProductServices_updategroup(Request $request, $id)
    {
        $getFeature = $request->featured;

        if (!$getFeature) {
            ProductGroupFeature::where('product_group_id', $id)->delete();
        } else {
            $comparedFeature = ProductGroupFeature::where('product_group_id', $id)->pluck('feature')->toArray();
            $filteredFeature = array_diff($comparedFeature, $getFeature);
            foreach ($filteredFeature as $key => $value) {
                ProductGroupFeature::where('product_group_id', $id)->where('product_group_id', $id)->delete();
            }
        }

        if ($getFeature) {
            foreach ($getFeature as $key => $value) {
                $checkProductFeature = ProductGroupFeature::where('product_group_id', $id)->where('feature', $value)->update(['feature' => $value]);
                if (!$checkProductFeature) {
                    $storeFeature = new ProductGroupFeature();
                    $storeFeature->product_group_id = $id;
                    $storeFeature->feature = $value;
                    $storeFeature->order = '';
                    $storeFeature->save();
                }
            }
        }

        $updatedProd = Productgroup::findOrFail($id);
        $updatedProd->name = $request->name;
        $updatedProd->headline = $request->headline ?? '';
        $updatedProd->tagline = $request->tagline ?? '';
        if ($request->orderfrmtpl != Cfg::getValue('OrderFormTemplate')) {
           $updatedProd->orderfrmtpl = $request->orderfrmtplcustom;
        } else {
         $updatedProd->orderfrmtpl = $request->orderfrmtpl;
        }
        if ($request->disabledgateways) {
            $activeGateway = $request->disabledgateways;
            $gateway = Gateway::GetGatewaysArray();
            $gatewayList = [];
            foreach ($gateway as $key => $value) {
                $gatewayList[] = $key;
            }
            $finalDisabledGateway = array_diff($gatewayList, $activeGateway);
            $filteredGateway = implode(",", $finalDisabledGateway);
            $updatedProd->disabledgateways = $filteredGateway ?? '';
        }
        $updatedProd->hidden = $request->hidden;

        $customtpl = $request->customtpl;
        API::post('SetConfigurationValue', ['setting' => 'OrderFormTemplate', 'value' => $customtpl]);
        $updatedProd->save();

        return redirect()->route('admin.pages.setup.prodsservices.productservices.index')->with(['success' => 'Product group has been updated!']);
    }
    public function ProductsServices_createproduct()
    {
        if (!auth()->user()->checkPermissionTo("Create New Products/Services")) {
            return redirect()
                ->route('admin.pages.setup.prodsservices.productservices.index')
                ->with(['error' => 'You don\'t have permission to access this area!']);
        }

        $server = new \App\Module\Server();
        $modules = $server->getListWithDisplayNames();

        $prodGroup = Productgroup::all();
        $prodId = Product::latest()->first();
        $prodId = $prodId ? $prodId->id : 0;
        return view('pages.setup.prodsservices.productservices.createproduct', ['prodGroup' => $prodGroup, 'prodId' => $prodId, 'modules' => $modules]);
    }
    public function ProductsServices_createproduct_add(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'type' => 'nullable|string',
            'gid' => 'nullable|numeric',
            'name' => 'required|string',
            'module' => 'nullable|string',
            'hidden' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.pages.setup.prodsservices.productservices.createproduct')->withErrors($validator)->with('message', 'Can\'t create new product, please fill your forms correctly and try again');
        }

        $newProds = new Product();
        $newProds->type = $request->type;
        $newProds->gid = $request->gid;
        $newProds->name = $request->name;
        $newProds->hidden = $request->hidden ?? 0;
        $newProds->save();
        $prodId = Product::latest()->first()->id;
        $prodGroup = Productgroup::all();
        $prodType = Product::all()->pluck('type')->toArray();
        
        $listTypeValue = array_unique($prodType);
        return redirect()->route('admin.pages.setup.prodsservices.productservices.createproduct.edit.prods', $prodId)->with([
            'newProds' => $newProds,
            'listTypeValue' => $listTypeValue,
            'prodGroup' => $prodGroup,
            'success' => 'A product successfully created!'
        ]);
    }

    public function ProductsService_createproduct_duplicate()
    {
        $products = HelpersProduct::getProducts();
        $prodId = Product::latest()->first()->id;
        // dd($prodId);
        return view('pages.setup.prodsservices.productservices.duplicateproduct', ['products' => $products, 'prodId' => $prodId]);
    }

    public function ProductsService_createproduct_duplicate_postOLD(Request $request)
    {
        $newProdName = $request->name;
        $existProduct = $request->existingproduct; // ID Product Selected From Dropdown 

        $newProds = Product::findOrFail($existProduct)->replicate();
        $existingProductName = $newProds->name;
        $newProds->name = $newProdName;
        $newProds->displayOrder++;
        Product::where('gid', $newProds->productGroupId)->where("order", ">=", $newProds->displayOrder)->increment("order");
        $newProds->save();
        $prodId = Product::latest()->first()->id;

        LogActivity::Save("Product Duplicated: '$existingProductName'  to  '$newProdName' - Product ID: $prodId");
        return redirect()->route('admin.pages.setup.prodsservices.productservices.createproduct.edit.prods', ['id' => $prodId])->with([
            'success' => 'Product ' . $existingProductName . ' succesfully duplicated.'
        ]);
    }
    public function ProductsService_createproduct_duplicate_post(Request $request)
    {
        $newProdName = $request->name;
        $existProduct = $request->existingproduct; // ID Product Selected From Dropdown 

        $existingproduct = $request->existingproduct;
        $newproductname = $request->name;
        DB::beginTransaction();
        try {
            $newProduct = \App\Models\Product::findOrFail($existingproduct)->replicate();
            $existingProductName = $newProduct->name;
            $newProduct->name = $newproductname;
            $newProduct->displayOrder++;
            // \App\Models\Product::where("gid", $newProduct->productGroupId)->where("order", ">=", $newProduct->displayOrder)->increment("order");
            $newProduct->save();
            $newproductid = $newProduct->id;
            $result = \App\Models\Pricing::where(array("type" => "product", "relid" => $existingproduct))->get();
            foreach ($result->toArray() as $data) {
                unset($data['id']);
                $data['relid'] = $newproductid;
                \App\Models\Pricing::insert($data);
                // $addstr = "";
                // foreach ($data as $key => $value) {
                //     if (is_numeric($key)) {
                //         if ($key == "0") {
                //             $value = "";
                //         }
                //         if ($key == "3") {
                //             $value = $newproductid;
                //         }
                //         $addstr .= "'" . \App\Helpers\Database::db_escape_string($value) . "',";
                //     }
                // }
                // $addstr = substr($addstr, 0, -1);
                // DB::statement("INSERT INTO tblpricing VALUES (" . $addstr . ")");
            }
            $result2 = \App\Models\Customfield::where(array("type" => "product", "relid" => $existingproduct))->orderBy("id", "ASC")->get();
            foreach ($result2->toArray() as $data) {
                unset($data['id']);
                $data['relid'] = $newproductid;
                \App\Models\Customfield::insert($data);
                // $addstr = "";
                // foreach ($data as $key => $value) {
                //     if (is_numeric($key)) {
                //         if ($key == "0") {
                //             $value = "";
                //         }
                //         if ($key == "2") {
                //             $value = $newproductid;
                //         }
                //         $addstr .= "'" . \App\Helpers\Database::db_escape_string($value) . "',";
                //     }
                // }
                // $addstr = substr($addstr, 0, -1);
                // DB::statement("INSERT INTO tblcustomfields VALUES (" . $addstr . ")");
            }
            DB::commit();
        } catch (Exception $e) {
            \App\Helpers\AdminFunctions::logAdminActivity("Failed to duplicate product ID " . $existingproduct . ": " . $e->getMessage());
            DB::rollback();
            throw $e;
        }
        \App\Helpers\AdminFunctions::logAdminActivity("Product Duplicated: '" . $existingProductName . "' to '" . $newproductname . "' - Product ID: " . $newproductid);
        // redir("action=edit&id=" . $newproductid);
        return redirect()->route('admin.pages.setup.prodsservices.productservices.createproduct.edit.prods', ['id' => $newproductid])->with([
            'success' => 'Product ' . $existingProductName . ' succesfully duplicated.'
        ]);
    }

    public function ProductsServices_createproduct_edit(Request $request, $id)
    {
        $pfx = $this->prefix;
        $id = $id ?? $request->id;
        $newProds = Product::findOrFail($id);
        $prodGroup = Productgroup::all();
        $prodType = Product::all()->pluck('type')->toArray();
        $listTypeValue = array_unique($prodType);
        $customfields = \App\Models\Customfield::where(array("type" => "product", "relid" => $id))->orderBy("sortorder", "ASC")->orderBy("id", "ASC")->get();
        $server = new \App\Module\Server();
        $serverModules = $server->getListWithDisplayNames();
        $serverGroups = DB::table("tblservergroups")->join("tblservergroupsrel", "tblservergroups.id", "=", "tblservergroupsrel.groupid")->join("tblservers", "tblservergroupsrel.serverid", "=", "tblservers.id")->groupBy("tblservergroups.id")->selectRaw("tblservergroups.id,tblservergroups.name,CONCAT(\",\", GROUP_CONCAT(DISTINCT tblservers.type SEPARATOR \",\"), \",\") as server_types")->get();
        $customProductEmail = Emailtemplate::where('custom', 1)->where('type', 'product')->get();
        $cyclesPricing = ["monthly", "quarterly", "semiannually", "annually", "biennially", "triennially"];
  
        $prodsTypeDropdown = \App\Helpers\Product::productTypeDropDown($newProds->type ?? "");
        $polishedType = [];
        foreach ($listTypeValue as $key => $string) {
            switch ($string) {
                case 'server':
                    $polishedType[] =  'Server/VPS';
                    break;
                case 'hostingaccount':
                    $polishedType[] =  'Shared Hosting';
                    break;
                case 'resellerhosting':
                    $polishedType[] =  'Reseller Hosting';
                    break;
                case 'sharedhosting':
                    $polishedType[] =  'Shared Hosting';
                    break;
                default:
                    $polishedType[] = 'Other';
                    break;
            }
        }

        $prodTypeNew = $prodsTypeDropdown;

        // ? Pricing //
        $currencies = \App\Models\Currency::select('id', 'code')->get();
        $cycles = ["monthly", "quarterly", "semiannually", "annually", "biennially", "triennially"];
        $setup = [
            'msetupfee',
            'qsetupfee',
            'ssetupfee',
            'asetupfee',
            'bsetupfee',
            'tsetupfee'
        ];

        $price = array();
        $price['onetime'] = array();
        $price['recurring'] = array();

        foreach ($currencies as $r) {
            $price[$newProds->paytype][$r->id] = \App\Models\Pricing::where('type', 'product')->where('relid', $id)->where('currency', $r->id)->first();
        }
        // dd($price);

        $params = [
            'currencies' => $currencies,
            'cycles' => $cycles,
            'setup' => $setup
        ];

        // ? Configurable Options //
        $configList = Productconfiggroup::all();
        $configoptionlinks = array();
        $result = Productconfiglink::where('pid', $id)->get();
        foreach ($result as $row) {
            $configoptionlinks[] = $row->gid;
        }

        // ? Upgrades //
        $upgradesPackageList = DB::select(DB::raw("SELECT {$pfx}products.id, {$pfx}productgroups.name 
                        AS groupname,{$pfx}products.name 
                        AS productname 
                        FROM {$pfx}products 
                        INNER JOIN {$pfx}productgroups 
                        ON {$pfx}productgroups.id = {$pfx}products.gid 
                        ORDER BY {$pfx}productgroups.`order`,{$pfx}products.`order`,{$pfx}products.name ASC"));
        $getUpgradePackageId = ProductUpgradeProduct::where('product_id', $id)->get();


        // ? Free Domains//
        $tldList = Domainpricing::all();
        $paymentTerms = explode(',', $newProds->freedomainpaymentterms);
        // dd($paymentTerms);
        $tldInProduct = explode(',', $newProds->freedomaintlds);


        return view('pages.setup.prodsservices.productservices.editproduct', [
            'listTypeValue' => $prodTypeNew,
            'cyclesPricing' => $cyclesPricing,
            'prodGroup' => $prodGroup,
            'newProds' => $newProds,
            'configList' => $configList,
            'configOptionLinks' => $configoptionlinks,
            'customfields' => $customfields,
            'serverModules' => $serverModules,
            'serverGroups' => $serverGroups,
            'activeTab' => $request->tab,
            'upgradesPackageList' => $upgradesPackageList,
            'getUpgradePackageId' => $getUpgradePackageId,
            'customProductEmail' => $customProductEmail,
            'tlds' => $tldList,
            'tldInProduct' => $tldInProduct,
            'paymentTerms' => $paymentTerms,
            'params' => $params,
            'price' => $price
        ]);
    }
    public function ProductsServices_createproduct_edit_customfields(Request $request)
    {
        $relid = $request->input('id');
        $id = $relid;
        $tab = $request->input('tab');
        $url = url()->previous();
        $route = app('router')->getRoutes($url)->match(app('request')->create($url))->getName();

        DB::beginTransaction();
        try {
            $customfieldname = $request->input("fieldname") ?? [];
            if ($customfieldname) {
                $customfieldtype = $request->input("fieldtype");
                $customfielddesc = $request->input("description");
                $customfieldoptions = $request->input("fieldoptions");
                $customfieldregexpr = $request->input("regexpr");
                $customadminonly = $request->input("adminonly");
                $customrequired = $request->input("required");
                $customshoworder = $request->input("showorder");
                $customshowinvoice = $request->input("showinvoice");
                $customsortorder = $request->input("sortorder");
                foreach ($customfieldname as $fid => $value) {
                    $type = isset($customfieldtype[$fid]) ? $customfieldtype[$fid] : "";
                    $desc = isset($customfielddesc[$fid]) ? $customfielddesc[$fid] : "";
                    $op = isset($customfieldoptions[$fid]) ? $customfieldoptions[$fid] : "";
                    $regx = isset($customfieldregexpr[$fid]) ? $customfieldregexpr[$fid] : "";
                    $adminonly = isset($customadminonly[$fid]) ? $customadminonly[$fid] : "";
                    $required = isset($customrequired[$fid]) ? $customrequired[$fid] : "";
                    $showorder = isset($customshoworder[$fid]) ? $customshoworder[$fid] : "";
                    $showinvoice = isset($customshowinvoice[$fid]) ? $customshowinvoice[$fid] : "";
                    $sortorder = isset($customsortorder[$fid]) ? $customsortorder[$fid] : "";
                    $thisCustomField = \App\Models\Customfield::findOrFail($fid);
                    if ($value != $thisCustomField->fieldname) {
                        $changes[] = "Custom Field Name Modified: '" . $thisCustomField->fieldname . "' to '" . $value . "'";
                    }
                    if ($type != $thisCustomField->fieldtype || $desc != $thisCustomField->description || $op != $thisCustomField->fieldoptions || $regx != $thisCustomField->regexpr || $adminonly != $thisCustomField->adminonly || $required != $thisCustomField->required || $showorder != $thisCustomField->showorder || $showinvoice != $thisCustomField->showinvoice || $sortorder != $thisCustomField->sortorder) {
                        $changes[] = "Custom Field Modified: '" . $value . "'";
                    }
                    \App\Models\Customfield::where(array("id" => $fid))->update(array("fieldname" => $value, "fieldtype" => $type, "description" => $desc, "fieldoptions" => $op, "regexpr" => \App\Helpers\Sanitize::decode($regx), "adminonly" => $adminonly, "required" => $required, "showorder" => $showorder, "showinvoice" => $showinvoice, "sortorder" => $sortorder));
                }
            }
            $addfieldname = $request->input("addfieldname");
            if ($addfieldname) {
                $addfieldtype = $request->input("addfieldtype") ?? "product";
                $addcustomfielddesc = $request->input("adddescription") ?? "";
                $addfieldoptions = $request->input("addfieldoptions") ?? "";
                $addregexpr = $request->input("addregexpr") ?? "";
                $addadminonly = $request->input("addadminonly") ?? "";
                $addrequired = $request->input("addrequired") ?? "";
                $addshoworder = $request->input("addshoworder") ?? "";
                $addshowinvoice = $request->input("addshowinvoice") ?? "";
                $addsortorder = $request->input("addsortorder") ?? "";
                $changes[] = "Custom Field Created: '" . $addfieldname . "'";
                $customFieldIDid = \App\Models\Customfield::insertGetId(array("type" => "product", "relid" => $id, "fieldname" => $addfieldname, "fieldtype" => $addfieldtype, "description" => $addcustomfielddesc, "fieldoptions" => $addfieldoptions, "regexpr" => \App\Helpers\Sanitize::decode($addregexpr), "adminonly" => $addadminonly, "required" => $addrequired, "showorder" => $addshoworder, "showinvoice" => $addshowinvoice, "sortorder" => $addsortorder));
                if (\App\Helpers\Cfg::getValue("EnableTranslations")) {
                    // TODO: WHMCS\Language\DynamicTranslation::saveNewTranslations($customFieldIDid, array("custom_field.{id}.name", "custom_field.{id}.description"));
                }
            }

            if ($changes) {
                \App\Helpers\AdminFunctions::logAdminActivity("Product Configuration Modified: " . implode(". ", $changes) . ". Product ID: " . $relid);
            }

            DB::commit();
            return redirect()->route($route, ['id' => $relid, 'tab' => $tab])->with(['success' => 'Your changes have been saved.']);
        } catch (\Exception $e) {
            // dd($e);
            DB::rollBack();
            return redirect()->route($route, ['id' => $relid, 'tab' => $tab])->with(['error' => $e->getMessage()]);
        }
    }
    public function ProductsServices_createproduct_edit_customfieldsOLD(Request $request)
    {
        $relid = $request->input('id');
        $tab = $request->input('tab');
        $url = url()->previous();
        $route = app('router')->getRoutes($url)->match(app('request')->create($url))->getName();

        DB::beginTransaction();
        try {
            // save fieldname
            foreach ($request->input('fieldname') ?? [] as $id => $value) {
                $customfield = \App\Models\Customfield::findOrFail($id);
                $customfield->fieldname = $value;
                $customfield->save();
            }
            // save sortorder
            foreach ($request->input('sortorder') ?? [] as $id => $value) {
                $customfield = \App\Models\Customfield::findOrFail($id);
                $customfield->sortorder = $value;
                $customfield->save();
            }
            // save fieldtype
            foreach ($request->input('fieldtype') ?? [] as $id => $value) {
                $customfield = \App\Models\Customfield::findOrFail($id);
                $customfield->fieldtype = $value;
                $customfield->save();
            }
            // save description
            foreach ($request->input('description') ?? [] as $id => $value) {
                $customfield = \App\Models\Customfield::findOrFail($id);
                $customfield->description = $value;
                $customfield->save();
            }
            // save adminonly
            // HOTFIX: this
            foreach ($request->input('adminonly') ?? [] as $id => $value) {
                $customfield = \App\Models\Customfield::findOrFail($id);
                $customfield->adminonly = $value;
                $customfield->save();
            }
            // save required
            foreach ($request->input('required') ?? [] as $id => $value) {
                $customfield = \App\Models\Customfield::findOrFail($id);
                $customfield->required = $value;
                $customfield->save();
            }
            // save showorder
            foreach ($request->input('showorder') ?? [] as $id => $value) {
                $customfield = \App\Models\Customfield::findOrFail($id);
                $customfield->showorder = $value;
                $customfield->save();
            }
            // save showinvoice
            foreach ($request->input('showinvoice') ?? [] as $id => $value) {
                $customfield = \App\Models\Customfield::findOrFail($id);
                $customfield->showinvoice = $value;
                $customfield->save();
            }

            // add new
            if ($request->input('addfieldname')) {
                DB::table(Database::prefix() . "customfields")->insert([
                    'type' => 'product',
                    'relid' => $relid,
                    'fieldname' => $request->input('addfieldname'),
                    'fieldtype' => $request->input('addfieldtype') ?? '',
                    'description' => $request->input('adddescription') ?? '',
                    'regexpr' => $request->input('addregexpr') ?? '',
                    'fieldoptions' => $request->input('addfieldoptions') ?? '',
                    'adminonly' => $request->input('addadminonly') ?? '',
                    'required' => $request->input('addrequired') ?? '',
                    'showorder' => $request->input('addshoworder') ?? '',
                    'showinvoice' => $request->input('addshowinvoice') ?? '',
                    'sortorder' => $request->input('addsortorder') ?? 0,
                    'created_at' => \Carbon\Carbon::now(),
                ]);
            }

            DB::commit();
            return redirect()->route($route, ['id' => $relid, 'tab' => $tab])->with(['success' => 'Your changes have been saved.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route($route, ['id' => $relid, 'tab' => $tab])->with(['error' => $e->getMessage()]);
        }
    }

    public function ProductsServices_createproduct_edit_details(Request $request, $id)
    {
        $request->all();
        $tab = $request->input('tab');
        $url = url()->previous();
        $route = app('router')->getRoutes($url)->match(app('request')->create($url))->getName();
        $updateData = Product::findOrFail($id);
        $updateData->type = $request->type;
        $updateData->gid = $request->gid;
        $updateData->name = $request->name;
        $updateData->qty = $request->qty;
        $updateData->description = $request->description;
        $updateData->welcomeemail = $request->welcomeemail;
        $updateData->showdomainoptions = $request->showdomainoptions;
        $updateData->stockcontrol = $request->stockcontrol;
        $updateData->tax = $request->tax;
        $updateData->is_featured = $request->is_featured;
        $updateData->hidden = $request->hidden;
        $updateData->retired = $request->retired;
        $updateData->save();

        return redirect()->route($route, ['id' => $id, 'tab' => $tab])->with(['success' => 'A product successfully updated!']);
    }
    public function ProductsServices_createproduct_edit_modulesettings(Request $request)
    {
        $tab = $request->input('tab');
        $id = $request->input('id');
        $url = url()->previous();
        $route = app('router')->getRoutes($url)->match(app('request')->create($url))->getName();

        DB::beginTransaction();
        try {
            $product = \App\Models\Product::find($id);
            if (!$product) {
                return redirect()->route($route, ['id' => $id, 'tab' => $tab])->with(['error' => 'Product Not Found']);
            }

            $table = "tblproducts";
            $changes = array();
            $array = array();
            $autosetup = $request->input("autosetup");
            $servertype = $request->input("servertype") ?? "";
            $servergroup = (int) $request->input("servergroup");
            $packageconfigoption = $request->input("packageconfigoption") ?: array();
            $array["servertype"] = $servertype;
            if ($servergroup != $product->serverGroupId) {
                $changes[] = "Server Group Modified: '" . $product->serverGroupId . "' to '" . $servergroup . "'";
            }
            $array["servergroup"] = $servergroup;
            if ($request->has("autosetup") && $autosetup != $product->autoSetup) {
                if (!$autosetup) {
                    $changes[] = "Automatic Setup Disabled";
                } else {
                    $changes[] = "Automatic Setup Modified: '" . ucfirst($product->autoSetup) . "' to '" . ucfirst($autosetup) . "'";
                }
                $array["autosetup"] = $autosetup;
            }
            $hasServerTypeChanged = $servertype != $product->module;
            $server = new \App\Module\Server();
            $newServer = $server->load($servertype);
            if ($hasServerTypeChanged) {
                $oldServer = new \App\Module\Server();
                $oldName = $oldServer->load($product->module) ? $oldServer->getDisplayName() : "";
                $newName = $newServer ? $server->getDisplayName() : "";
                $changes[] = "Server Module Modified: '" . $oldName . "' to '" . $newName . "'";
            }
            if ($server->functionExists("ConfigOptions")) {
                $configArray = $server->call("ConfigOptions", array("producttype" => $product->type));
                $counter = 0;
                foreach ($configArray as $key => $values) {
                    $counter++;
                    $mco = "moduleConfigOption" . $counter;
                    if (!isset($packageconfigoption[$counter])) {
                        $packageconfigoption[$counter] = $product->{$mco};
                    }
                    // if (!$request->has("packageconfigoption.{$counter}")) {
                    //     $packageconfigoption[$counter] = $product->{$mco};
                    // }
                    // if (!$whmcs->isInRequest("packageconfigoption", $counter)) {
                    //     $packageconfigoption[$counter] = $product->{$mco};
                    // }
                    $saveValue = is_array($packageconfigoption[$counter]) ? $packageconfigoption[$counter] : trim($packageconfigoption[$counter]);
                    if (!$hasServerTypeChanged) {
                        if ($values["Type"] == "password") {
                            $field = "configoption" . $counter;
                            $existingValue = $product->{$field};
                            $updatedPassword = \App\Helpers\AdminFunctions::interpretMaskedPasswordChangeForStorage($saveValue, $existingValue);
                            if ($updatedPassword === false) {
                                continue;
                            }
                            if ($updatedPassword) {
                                $changes[] = (string) $key . " Value Modified";
                            }
                        } else {
                            if (is_array($saveValue)) {
                                $saveValue = json_encode($saveValue);
                                if ($saveValue != $product->{$mco}) {
                                    $changes[] = (string) $key . " Value Modified";
                                }
                            } else {
                                $saveValue = \App\Helpers\Sanitize::decode($saveValue);
                                if ($saveValue != $product->{$mco}) {
                                    $changes[] = (string) $key . " Value Modified: '" . $product->{$mco} . "' to '" . $saveValue . "'";
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
                    $array["configoption" . $counter] = $saveValue;
                }
            }
            $where = array("id" => $id);
            // update_query($table, $array, $where);
            \App\Models\Product::where($where)->update($array);
            $product->save($array);

            DB::commit();
            return redirect()->route($route, ['id' => $id, 'tab' => $tab])->with(['success' => 'Your changes have been saved.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route($route, ['id' => $id, 'tab' => $tab])->with(['error' => $e->getMessage()]);
        }
    }
    public function ProductsServices_createproduct_update(Request $request, $id)
    {
        $data = $request->all();
        $updateData = Product::findOrFail($id);
        $updateData->update($data);
        return redirect()->route('admin.pages.setup.prodsservices.productservices.index')->with(['success' => 'A new product successfully updated!']);
    }

    public function ProductsServices_createproduct_edit_configurableoptions(Request $request, $id)
    {
        $array =  array();
        $changes = array();
        $request->all();
        $tab = $request->input('tab');
        $url = url()->previous();
        $route = app('router')->getRoutes($url)->match(app('request')->create($url))->getName();

        // ? Selected Value From Multiple Select //
        $groupSelected = $request->configoptionlinks;
        $productConfigOptionsChanged = false;
        $existingConfigLinks = array();

        // ? Update Configlink //
        $configLinkChecks = Productconfiglink::where('pid', $id)->get();
        if ($groupSelected) {
            foreach ($configLinkChecks as $configLinkCheck) {
                if (!in_array($configLinkCheck->gid, $groupSelected) && $productConfigOptionsChanged === false) {
                    $productConfigOptionsChanged = true;
                    $changes[] = "Assigned Configurable Option Groups Modified";
                }
                $existingConfigLinks[] = $configLinkCheck->gid;
            }
        }
        Productconfiglink::where('pid', $id)->delete();

        // ? Insert New Configlink //
        if ($groupSelected) {
            foreach ($groupSelected as $key => $groupId) {
                if (!in_array($groupId, $existingConfigLinks) && $productConfigOptionsChanged === false) {
                    $productConfigOptionsChanged = true;
                    $changes[] = "Assigned Configurable Option Groups Modified";
                }
                $configLink = new Productconfiglink();
                $configLink->gid = $groupId;
                $configLink->pid = $id;
                $configLink->save();
            }
        }
        \App\Helpers\Hooks::run_hook('ProductEdit', array_merge(array('pid' => $id), $array));
        \App\Helpers\Hooks::run_hook("AdminProductConfigFieldsSave", array("pid" => $id));

        if ($changes) {
            LogActivity::Save("Product Configuration Modified: " . implode(". ", $changes) . ". Product ID: " . $id);
        }

        return redirect()->route($route, ['id' => $id, 'tab' => $tab])->with(['success' => 'A product successfully updated!']);
    }

    public function ProductsServices_createproduct_edit_freedomains(Request $request, $id)
    {
        $request->all();
        $tab = $request->input('tab');
        $url = url()->previous();
        $route = app('router')->getRoutes($url)->match(app('request')->create($url))->getName();
        // dd($request->all());
        $updatedData = Product::findOrFail($id);
        if ($request->freedomain == 'none') {
            $updatedData->freedomain = "";
        } else {
            $updatedData->freedomain = $request->freedomain;
        }
        if ($request->freedomainpaymentterms) {
            $strFreedomainPaymentTerms = implode(",", $request->freedomainpaymentterms);
        }
        $updatedData->freedomainpaymentterms = $strFreedomainPaymentTerms ?? '';
        if ($request->freedomaintlds) {
            $strFreedomainTlds = implode(",", $request->freedomaintlds);
        }
        $updatedData->freedomaintlds = $strFreedomainTlds ?? '';
        $updatedData->save();

        return redirect()->route($route, ['id' => $id, 'tab' => $tab])->with(['success' => 'A product successfully updated!']);
    }

    public function ProductsServices_createproduct_edit_upgrades(Request $request, $id)
    {
        $request->all();
        $tab = $request->input('tab');
        $url = url()->previous();
        $route = app('router')->getRoutes($url)->match(app('request')->create($url))->getName();

        $productData = Product::findOrFail($id);

        $oldUpgradeProductIds = array();
        foreach ($productData->upgradeProducts as $oldUpgradeProduct) {
            $oldUpgradeProductIds[] = $oldUpgradeProduct->id;
        }
        $upgradepackages = $request->upgradepackages;
        if ($upgradepackages) {
            $productData->upgradeProducts()->detach();
            foreach ($upgradepackages as $upgradepackageId) {
                $productData->upgradeProducts()->attach(Product::find($upgradepackageId));
            }
        }

        $productData->configoptionsupgrade = (int) $request->configoptionsupgrade;
        $productData->upgradeemail = $request->upgradeemail ?? 0;
        $productData->save();
        return redirect()->route($route, ['id' => $id, 'tab' => $tab])->with(['success' => 'A product successfully updated!']);
    }

    public function ProductsServices_createproduct_edit_pricing(Request $request, $id)
    {
        $request->all();
        // dd($request->recurringcycles);
        $tab = $request->input('tab');
        $url = url()->previous();
        $route = app('router')->getRoutes($url)->match(app('request')->create($url))->getName();
        $changedRecurring = false;

        $updateData = Product::findOrFail($id);
        $productName = $request->name;
        $updateData->paytype = $request->paytype;
        $updateData->allowqty = $request->allowqty;
        $updateData->recurringcycles = $request->recurringcycles;
        $updateData->autoterminatedays = $request->autoterminatedays;
        $updateData->autoterminateemail = $request->autoterminateemail;
        $updateData->proratabilling = $request->proratabilling;
        $updateData->proratadate = $request->proratadate;
        $updateData->proratachargenextmonth = $request->proratachargenextmonth;
        $updateData->save();

        if ($updateData->paytype == 'recurring') {
            $changedRecurring = true;
        }

        $pricingUpdated = false;

        if ($request->currency) {
            $dataCurrencies = $request->currency;

            foreach ($dataCurrencies as $currency_id => $pricing) {
                $arrProdUpdate = array("type" => "product", "currency" => $currency_id, "relid" => $id);
                $initPrice = array_merge($pricing, $arrProdUpdate);
                $productPricing = \App\Models\Pricing::where('type', 'product')->where('currency', $currency_id)->where('relid', $id)->first();

                if ($productPricing) {
                    \App\Models\Pricing::where('type', 'product')->where('currency', $currency_id)->where('relid', $id)->update($initPrice);
                } else {
                    \App\Models\Pricing::where('type', 'product')->where('currency', $currency_id)->where('relid', $id)->insert($initPrice);
                }


                foreach ($pricing as $keyName => $value) {
                    if ((@$productPricing->{$keyName} != $value || $changedRecurring) && !$pricingUpdated) {
                        LogActivity::save("Product " . $productName . " Payment Type Modified");
                        $pricingUpdated = true;
                        break;
                    }
                }

                \App\Models\Pricing::where('type', 'product')->where('currency', $currency_id)->where('relid', $id)->update($initPrice);
            }
        }

        return redirect()->route($route, ['id' => $id, 'tab' => $tab])->with(['success' => 'A product successfully updated!']);
    }

    public function ProductServices_createproduct_delete(Request $request, $id)
    {
        $data = $request->ajax();
        $productData = Product::findOrFail($id);
        $productData->delete();
        return redirect()->route('admin.pages.setup.prodsservices.productservices.index')->with(['success' => 'Selected product successfully deleted!']);
    }
}

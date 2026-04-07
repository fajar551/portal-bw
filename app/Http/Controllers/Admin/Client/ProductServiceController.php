<?php

namespace App\Http\Controllers\Admin\Client;

use App\Helpers\AdminFunctions;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// Helpers
use App\Helpers\Format;
use App\Helpers\Functions;
use App\Helpers\Client;
use App\Helpers\ClientHelper;
use App\Helpers\Cycles;
use App\Helpers\Gateway;
use App\Helpers\Product;
use App\Helpers\ResponseAPI;

// Models
use App\Models\Addon;
use App\Models\Customfield;
use App\Models\Hosting;
use App\Models\Hostingaddon;

// Traits
use App\Traits\DatatableFilter;

class ProductServiceController extends Controller
{
    
    use DatatableFilter;

    public function __construct() {
        $this->middleware(['auth:admin']);
        $this->prefix = \Database::prefix();
    }

    public function index($serviceType = "")
    {
        $predefinedAddonsList = Addon::pluck("name", "id")->toArray();
        $predefinedAddonsList += Hostingaddon::where("name", "!=", "")->pluck("name", "name")->toArray();
        $serverData = AdminFunctions::getServerDropdownOptions();

        $templatevars = [
            "addonsList" => $predefinedAddonsList,
            "products" => Product::productDropDown(),
            "productsType" => Product::productTypeDropDown($serviceType),
            "domainstatus" => Product::productStatusDropDown(),
            "paymentMethods" => (new Gateway(request()))->paymentMethodsSelection(),
            "cycles" => Cycles::cyclesDropDown(),
            "customFields" => Customfield::where("type", "product")->get(),
            "servers" => $serverData["servers"] .$serverData["disabledServers"],
        ];

        switch ($serviceType) {
            case "hostingaccount":
            case "sharedhosting":
                $templatevars["pageTitle"] = __("admin.serviceslisthosting");
                break;
            case "reselleraccount":
                $templatevars["pageTitle"] = __("admin.serviceslistreseller");
                break;
            case "server":
            case "vpsservers":
                $templatevars["pageTitle"] = __("admin.serviceslistservers");
                break;
            case "other":
            case "otherservices":
                $templatevars["pageTitle"] = __("admin.serviceslistother");
                break;
            default:
                $templatevars["pageTitle"] = __("admin.servicestitle");
                break;
        }
        
        return view('pages.clients.productservices.index', $templatevars);
    }

    /*
    public function ProductServices()
    {
        return view('pages.clients.productservices.index');
    }
    public function ProductServices_sharedhosting()
    {
        return view('pages.clients.productservices.sharedhosting.index');
    }
    public function ProductServices_reselleraccount()
    {
        return view('pages.clients.productservices.reselleraccount.index');
    }
    public function ProductServices_vpsservers()
    {
        return view('pages.clients.productservices.vpsservers.index');
    }
    public function ProductServices_otherservices()
    {
        return view('pages.clients.productservices.otherservices.index');
    }
    */

    public function serviceDetail(Request $request)
    {
        $service = Hosting::with("order", "serverModel", "paymentGateway", "promotion")->find($request->get("serviceid"));
        $data = [];

        if (!$service) {
            return ResponseAPI::Error([
                'message' => "Invalid ID",
                'data' => $data,
            ]);
        }

        $data = [
            "fieldsordernum" => __("admin.fieldsordernum") .": " .($service->orderid ? $service->order()->first()->orderNumber : "-"), 
            "fieldsregdate" => __("admin.fieldsregdate") .": " .(new Client())->fromMySQLDate($service->regdate),
            "fieldsserver" => __("admin.fieldsserver") .": " .($service->serverModel()->first()->name ?? "-"), 
            "fieldsdedicatedip" => __("admin.fieldsdedicatedip") .": " .($service->dedicatedip ? $service->dedicatedip : "-"), 
            "fieldsusername" => __("admin.fieldsusername") .": " .($service->username ? $service->username : "-"), 
            "fieldspaymentmethod" => __("admin.fieldspaymentmethod") .": " .($service->paymentGateway()->name()->first()->value ?? "-"),
            "fieldspromocode" => __("admin.fieldspromocode") .": " .($service->promoid ? $service->promotion()->first()->code : "-"),
        ];

        return ResponseAPI::Success([
            'message' => "Success",
            'data' => $data,
        ]);
    }

    public function dtProductService(Request $request)
    {
        $pfx = $this->prefix;
        $dataFiltered = $request->dataFiltered;
        $params = [
            "pfx" => $pfx,
        ];

        $query = Hosting::select("{$pfx}hosting.*", "{$pfx}hosting.domainstatus as domstatus", "{$pfx}clients.firstname", "{$pfx}clients.lastname", "{$pfx}clients.companyname", "{$pfx}clients.groupid", "{$pfx}clients.currency", "{$pfx}products.name", "{$pfx}products.type", "{$pfx}products.servertype")
                            ->join("{$pfx}clients", "{$pfx}clients.id", "=", "{$pfx}hosting.userid")
                            ->join("{$pfx}products", "{$pfx}products.id", "=", "{$pfx}hosting.packageid");

        $filters = $this->dtProductServiceFilters($dataFiltered, $query);
        if ($filters) {
            $query->whereRaw($filters);
        }

        return datatables()->of($query)
            ->editColumn('name', function($row) use($params) {
                return $row->name;
            })
            ->editColumn('domain', function($row) {
                $domain = !$row->domain ? "(" .__("admin.addonsnodomain") .")" : $row->domain;
                $route = route('admin.pages.clients.viewclients.clientservices.index', [
                    'userid' => $row->userid, 
                    'id' => $row->id,
                ]);
                
                $hostingLink = "<a href=\"{$route}\">{$domain}</a>";
                $linkValue = "";
                if ($row->type != "other") {
                    $style = "color:#cc0000";
                    $linkValue = " <a href=\"http://" . $domain . "\" target=\"_blank\" style=\"" . $style . ";\">" . "<small>www</small></a>";
                }

                return $hostingLink .$linkValue;
            })
            ->editColumn('amount', function($row) {
                $amount = $row->amount;
                if ($row->billingcycle == "One Time" || $row->billingcycle == "Free Account") {
                    $amount = $row->firstpaymentamount;
                }

                return Format::formatCurrency($amount, $row->currency);
            })
            ->editColumn('nextduedate', function($row) {
                $nextDueDate = $row->nextduedate;
                if ($row->billingcycle == "One Time" || $row->billingcycle == "Free Account") {
                    $nextDueDate = "0000-00-00";
                }

                return $nextDueDate == "0000-00-00" ? "-" : (new Client())->fromMySQLDate($nextDueDate);
            })
            ->editColumn('domstatus', function($row) {
                $labelClass = Functions::generateCssFriendlyClassName($row->domstatus);
                $status = "<span class=\"badge text-white label-$labelClass\">{$row->domstatus}</span>";

                return $status;
            })
            ->addColumn('clientname', function($row) {
                return ClientHelper::outputClientLink($row->userid, $row->firstname, $row->lastname, $row->companyname, $row->groupid);
            })
            ->editColumn('billingcycle', function($row) {
                return __("admin.billingcycles" .str_replace(["-", "account", " "], "", strtolower($row->billingcycle)));
            })
            ->addColumn('raw_id', function($row) {
                $route = route('admin.pages.clients.viewclients.clientservices.index', [
                            'userid' => $row->userid, 
                            'id' => $row->id,
                        ]);
    
                $hostingIdLink = "<a href=\"{$route}\">{$row->id}</a>";

                return $hostingIdLink;
            })
            ->addColumn('actions', function($row) {
                $route = "javascript:void(0)";
                $action = "";

                $action .= "<a href=\"{$route}\" type=\"button\" class=\"btn btn-xs text-black p-1 act-detail\" data-id=\"{$row->id}\" title=\"Detail\" onclick=\"detail(this);\"><i class=\"fa fa-plus\"></i></a> ";
                
                return $action;
            })
            ->orderColumn('raw_id', function($query, $order) {
                $query->orderBy('id', $order);
            })
            ->orderColumn('clientname', function($query, $order) {
                $query->orderBy('firstname', $order);
            })
            ->rawColumns(['raw_id', 'clientname', 'domain', 'actions', 'domstatus'])
            ->addIndexColumn()
            ->toJson();
    }

    private function dtProductServiceFilters($criteria, $query = null)
    {
        $filters = [];
        $pfx = $this->prefix;

        if (isset($criteria["domainstatus"]) && $criteria["domainstatus"] != "Any") {
            $filters[] = $this->filterValue("domainstatus", "=", "'{$criteria["domainstatus"]}'"); 
        }

        if (isset($criteria["productname"]) && $criteria["productname"] != "Any") {
            $filters[] = $this->filterValue("{$pfx}products.name", "=", "'{$criteria["productname"]}'"); 
        }

        if (isset($criteria["package"]) && $criteria["package"] != "Any") {
            $filters[] = $this->filterValue("{$pfx}products.id", "=", "'{$criteria["package"]}'");
        }
        
        if (isset($criteria["paymentmethod"]) && $criteria["paymentmethod"] != "Any") {
            $filters[] = $this->filterValue("paymentmethod", "=", "'{$criteria["paymentmethod"]}'");
        }
        
        if (isset($criteria["domain"])) {
            $filters[] = $this->filterValue("domain", "LIKE", "'%{$criteria["domain"]}%'");
        }

        if (isset($criteria["nextduedate"])) {
            $filters[] = $this->filterValue("nextduedate", "=", (new \App\Helpers\SystemHelper())->toMySQLDate($criteria["nextduedate"]));
        }

        if (isset($criteria["username"])) {
            $filters[] = $this->filterValue("username", "=", "'{$criteria["username"]}'");
        }

        if (isset($criteria["dedicatedip"])) {
            $filters[] = $this->filterValue("dedicatedip", "=", "'{$criteria["dedicatedip"]}'");
        }

        if (isset($criteria["assignedips"])) {
            $filters[] = $this->filterValue("assignedips", "LIKE", "'%{$criteria["assignedips"]}%'");
        }

        if (isset($criteria["id"])) {
            $filters[] = $this->filterValue("{$pfx}hosting.id", "=", "'%{$criteria["id"]}%'");
        }

        if (isset($criteria["subscriptionid"])) {
            $filters[] = $this->filterValue("subscriptionid", "=", "'{$criteria["subscriptionid"]}'");
        }

        if (isset($criteria["notes"])) {
            $filters[] = $this->filterValue("{$pfx}hosting.notes", "LIKE", "'%{$criteria["notes"]}%'"); 
        }

        if (isset($criteria["clientname"])) {
            $filters[] = $this->filterValue("concat(firstname, ' ', lastname)", "LIKE", "'%{$criteria["clientname"]}%'"); 
        }

        if (isset($criteria["type"]) && $criteria["type"] != "Any") {
            $filters[] = $this->filterValue("{$pfx}products.type", "=", "'{$criteria["type"]}'");
        }
        
        if (isset($criteria["server"]) && $criteria["server"] != "Any") {
            $filters[] = $this->filterValue("server", "=", "'{$criteria["server"]}'");
        }

        if (isset($criteria["billingcycle"]) && $criteria["billingcycle"] != "Any") {
            $filters[] = $this->filterValue("billingcycle", "=", "'{$criteria["billingcycle"]}'");
        }

        if (isset($criteria["customfieldvalue"])) {
            if (isset($criteria["customfield"]) && $criteria["customfield"] != "Any") {
                $ids = \DB::table("{$pfx}customfieldsvalues")
                        ->where("fieldid", (int) $criteria["customfield"])
                        ->where("value", "like", "%{$criteria["customfieldvalue"]}%")
                        ->pluck("relid");
            } else {
                $ids = \DB::table("{$pfx}customfieldsvalues")
                        ->join("{$pfx}customfields", "{$pfx}customfields.id", "=", "{$pfx}customfieldsvalues.fieldid")
                        ->where("{$pfx}customfields.type", "product")
                        ->where("{$pfx}customfieldsvalues.value", "LIKE", "%{$criteria["customfieldvalue"]}%")
                        ->pluck("{$pfx}customfieldsvalues.relid");
            }

            $query->whereIn("{$pfx}hosting.id", $ids);
        }

        return $this->buildRawFilters($filters);
    }
    
}

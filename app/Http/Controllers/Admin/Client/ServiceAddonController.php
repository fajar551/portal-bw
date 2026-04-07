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
use App\Models\Hostingaddon;

// Traits
use App\Traits\DatatableFilter;

class ServiceAddonController extends Controller
{
    
    use DatatableFilter;

    public function __construct() {
        $this->middleware(['auth:admin']);
        $this->prefix = \Database::prefix();
    }

    public function index()
    {
        $predefinedAddonsList = Addon::pluck("name", "id")->toArray();
        $predefinedAddonsList += Hostingaddon::where("name", "!=", "")->pluck("name", "name")->toArray();
        $serverData = AdminFunctions::getServerDropdownOptions();

        $templatevars = [
            "addonsList" => $predefinedAddonsList,
            "products" => Product::productDropDown(),
            "productsType" => Product::productTypeDropDown(),
            "statusaddons" => Product::productStatusDropDown(),
            "paymentMethods" => (new Gateway(request()))->paymentMethodsSelection(),
            "cycles" => Cycles::cyclesDropDown(),
            "customFields" => Customfield::where("type", "addon")->get(),
            "servers" => $serverData["servers"] .$serverData["disabledServers"],
        ];

        return view('pages.clients.serviceaddons.index', $templatevars);
    }

    public function addonDetail(Request $request)
    {
        $addon = Hostingaddon::with("order", "service", "productAddon")->findOrFail($request->get("addonid"));
        $data = [];

        if (!$addon) {
            return ResponseAPI::Error([
                'message' => "Invalid ID",
                'data' => $data,
            ]);
        }

        $data = [
            "fieldsordernum" => __("admin.fieldsordernum") .": " .($addon->orderId ? $addon->order()->first()->orderNumber : "-"), 
            "fieldsregdate" => __("admin.fieldsregdate") .": " .(new Client())->fromMySQLDate($addon->registrationDate),
            "fieldsserver" => __("admin.fieldsserver") .": " .($addon->serverModel()->first()->name ?? "-"), 
            "fieldsparentdomain" => __("admin.fieldsparentdomain") .": " .($addon->service()->first()->domain ?? "-"),
            "fieldspaymentmethod" => __("admin.fieldspaymentmethod") .": " .($addon->paymentGateway()->name()->first()->value ?? "-"),
        ];

        return ResponseAPI::Success([
            'message' => "Success",
            'data' => $data,
        ]);
    }

    public function dtServiceAddons(Request $request)
    {
        $pfx = $this->prefix;
        $dataFiltered = $request->dataFiltered;
        $predefinedaddons = Addon::pluck("name", "id");
        $params = [
            "pfx" => $pfx,
            "predefinedaddons" => $predefinedaddons,
        ];

        $query = Hostingaddon::withoutAppends()
                        ->select("{$pfx}hostingaddons.*", "{$pfx}hostingaddons.name AS addonname", "{$pfx}hosting.domain", "{$pfx}hosting.userid", "{$pfx}clients.firstname", "{$pfx}clients.lastname", "{$pfx}clients.companyname", "{$pfx}clients.groupid", "{$pfx}clients.currency", "{$pfx}products.name", "{$pfx}products.type")
                        ->join("{$pfx}clients", "{$pfx}clients.id", "=", "{$pfx}hostingaddons.userid")
                        ->join("{$pfx}hosting", "{$pfx}hosting.id", "=", "{$pfx}hostingaddons.hostingid")
                        ->join("{$pfx}products", "{$pfx}products.id", "=", "{$pfx}hosting.packageid");

        $filters = $this->dtServiceAddonsFilters($dataFiltered, $query);
        if ($filters) {
            $query->whereRaw($filters);
        }

        return datatables()->of($query)
            ->editColumn('addonname', function($row) use($params) {
                if (!$row->addonname) {
                    return $params["predefinedaddons"][$row->addonid];
                }

                return $row->addonname;
            })
            ->editColumn('name', function($row) use($params) {
                $route = route('admin.pages.clients.viewclients.clientservices.index', [
                    'userid' => $row->userid, 
                    'id' => $row->hostingid,
                ]);

                return "<a href=\"{$route}\">{$row->name}</a>";
            })
            ->editColumn('domain', function($row) {
                return !$row->domain ? "(" .__("admin.addonsnodomain") .")" : $row->domain;
            })
            ->editColumn('recurring', function($row) {
                return Format::formatCurrency($row->recurring, $row->currency);
            })
            ->editColumn('nextduedate', function($row) {
                if (in_array($row->billingcycle, ["One Time", "Free Account", "Free"])) {
                    return "-";
                }

                return (new Client())->fromMySQLDate($row->nextduedate);
            })
            ->editColumn('status', function($row) {
                $labelClass = Functions::generateCssFriendlyClassName($row->status);
                $status = "<span class=\"badge text-white label-$labelClass\">{$row->status}</span>";

                return $status;
            })
            ->addColumn('clientname', function($row) {
                return ClientHelper::outputClientLink($row->userid, $row->firstname, $row->lastname, $row->companyname, $row->groupid);
            })
            ->editColumn('billingcycle', function($row) {
                return __("admin.billingcycles" .str_replace(["-", "account", " "], "", strtolower($row->billingcycle)));
            })
            ->addColumn('raw_id', function($row) {
                $route = route('admin.pages.clients.viewclients.clientservices.editAddon', [
                            'userid' => $row->userid, 
                            'id' => $row->hostingid, 
                            'aid' => $row->id
                        ]);

                return "<a href=\"{$route}\">{$row->id}</a>";
            })
            ->addColumn('actions', function($row) {
                $route = "javascript:void(0);";
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
            ->orderColumn('addonname', function($query, $order) use($params) {
                $query->orderBy("{$params["pfx"]}hostingaddons.name", $order);
            })
            ->orderColumn('name', function($query, $order) use($params) {
                $query->orderBy("{$params["pfx"]}products.name", $order);
            })
            ->rawColumns(['raw_id', 'clientname', 'name', 'domain', 'actions', 'status'])
            ->addIndexColumn()
            ->toJson();
    }

    private function dtServiceAddonsFilters($criteria, $query = null)
    {
        $filters = [];
        $pfx = $this->prefix;

        if (isset($criteria["statusclient"]) && $criteria["statusclient"] != "Any") {
            $filters[] = $this->filterValue("{$pfx}clients.status", "=", "'{$criteria["statusclient"]}'"); 
        }

        if (isset($criteria["addon"]) && $criteria["addon"] != "Any") {
            if (is_numeric($criteria["addon"])) {
                $filters[] = $this->filterValue("{$pfx}hostingaddons.addonid", "=", "'{$criteria["addon"]}'"); 
            } else {
                $filters[] = $this->filterValue("{$pfx}hostingaddons.name", "=", "'{$criteria["addon"]}'"); 
            }
        }

        if (isset($criteria["package"]) && $criteria["package"] != "Any") {
            $filters[] = $this->filterValue("{$pfx}products.id", "=", "'{$criteria["package"]}'");
        }
        
        if (isset($criteria["paymentmethod"]) && $criteria["paymentmethod"] != "Any") {
            $filters[] = $this->filterValue("{$pfx}hostingaddons.paymentmethod", "=", "'{$criteria["paymentmethod"]}'");
        }

        if (isset($criteria["statusaddons"]) && $criteria["statusaddons"] != "Any") {
            $filters[] = $this->filterValue("{$pfx}hostingaddons.status", "=", "'{$criteria["statusaddons"]}'");
        }
        
        if (isset($criteria["domain"])) {
            $filters[] = $this->filterValue("{$pfx}hosting.domain", "LIKE", "'%{$criteria["domain"]}%'");
        }

        if (isset($criteria["clientname"])) {
            $filters[] = $this->filterValue("concat(firstname, ' ', lastname)", "LIKE", "'%{$criteria["clientname"]}%'"); 
        }

        if (isset($criteria["type"]) && $criteria["type"] != "Any") {
            $filters[] = $this->filterValue("{$pfx}products.type", "=", "'{$criteria["type"]}'");
        }

        if (isset($criteria["server"]) && $criteria["server"] != "Any") {
            $params = [
                "server" => $criteria["server"],
                "pfx" => $pfx,
            ];

            $query->where(function($q) use($params) {
                extract($params);

                $q->where("{$pfx}hostingaddons.server", $server)->orWhere("{$pfx}hosting.server", $server);
            });
        }

        if (isset($criteria["billingcycle"]) && $criteria["billingcycle"] != "Any") {
            $filters[] = $this->filterValue("{$pfx}hostingaddons.billingcycle", "=", "'{$criteria["billingcycle"]}'");
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
                        ->where("{$pfx}customfields.type", "addon")
                        ->where("{$pfx}customfieldsvalues.value", "LIKE", "%{$criteria["customfieldvalue"]}%")
                        ->pluck("{$pfx}customfieldsvalues.relid");
            }

            $query->whereIn("{$pfx}hostingaddons.id", $ids);
        }

        return $this->buildRawFilters($filters);
    }
    
}

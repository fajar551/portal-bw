<?php

namespace App\Http\Controllers\Admin\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// Helpers
use App\Helpers\Carbon;
use App\Helpers\Domains;
use App\Helpers\Format;
use App\Helpers\Functions;
use App\Helpers\Client;
use App\Helpers\CoreDomains;
use App\Helpers\ResponseAPI;
use App\Helpers\WHOIS;

// Models
use App\Models\Domain;
use App\Module\Registrar;

// Traits
use App\Traits\DatatableFilter;

class DomainRegistrationController extends Controller
{
    
    use DatatableFilter;

    public function __construct() {
        $this->middleware(['auth:admin']);
        $this->prefix = \Database::prefix();
    }

    public function index()
    {
        $statuses = (new Domains())->translatedDropdownOptions();
        $registrars = (new Registrar())->getList();

        $templatevars["statuses"] = $statuses;
        $templatevars["registrars"] = $registrars;
        
        return view('pages.clients.domainregistrations.index', $templatevars);
    }

    public function dtDomainRegistration(Request $request)
    {
        $pfx = $this->prefix;
        $dataFiltered = $request->dataFiltered;
        $query = \DB::table("{$pfx}domains")->select("{$pfx}domains.*", "{$pfx}clients.groupid", "{$pfx}clients.currency", "{$pfx}clients.id as userid", "{$pfx}clients.firstname" , "{$pfx}clients.lastname", "{$pfx}clients.companyname")->join("{$pfx}clients", "{$pfx}clients.id", "{$pfx}domains.userid");

        $filters = $this->dtDomainRegistrationFilters($dataFiltered);
        if ($filters) {
            $query->whereRaw($filters);
        }

        return datatables()->of($query)
            ->editColumn('domain', function($row) {
                $route = route('admin.pages.clients.viewclients.clientdomain.index', ['domainid' => $row->id, 'userid' => $row->userid]);

                return !$row->domain ? "(" .__("admin.addonsnodomain") .")" : "<a href=\"{$route}\">{$row->domain}</a>";
            })
            ->editColumn('registrationperiod', function($row) {
                $regperiod = $row->registrationperiod;
                $yearOrYears = "admin.domainsyear" .($regperiod > 1 ? "s" : "");

                return "$regperiod " .__($yearOrYears);
            })
            ->editColumn('registrationdate', function($row) {
                return (new Client())->fromMySQLDate($row->registrationdate);
            })
            ->editColumn('nextduedate', function($row) {
                return (new Client())->fromMySQLDate($row->nextduedate);
            })
            ->editColumn('expirydate', function($row) {
                return (new Client())->fromMySQLDate($row->expirydate);
            })
            ->editColumn('recurringamount', function($row) {
                return Format::formatCurrency($row->recurringamount, $row->currency);
            })
            ->editColumn('registrar', function($row) {
                /*
                $registrarInterface = new \WHMCS\Module\Registrar();            
                $registrarLabel = ucfirst($row->registrar);
                if ($registrarInterface->load($row->registrar)) {
                    $registrarLabel = $registrarInterface->getDisplayName();
                }
                */
                
                return ucfirst($row->registrar);
            })
            ->editColumn('status', function($row) {
                $labelClass = Functions::generateCssFriendlyClassName($row->status);
                $status = "<span class=\"badge text-white label-$labelClass\">{$row->status}</span>";

                return $status;
            })
            ->addColumn('clientname', function($row) {
                return \App\Helpers\ClientHelper::outputClientLink($row->userid, $row->firstname, $row->lastname, $row->companyname, $row->groupid);
            })
            ->addColumn('raw_id', function($row) {
                $route = route('admin.pages.clients.viewclients.clientdomain.index', ['domainid' => $row->id, 'userid' => $row->userid]);

                return "<a href=\"{$route}\">{$row->id}</a>";
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
            ->rawColumns(['raw_id', 'clientname', 'domain', 'actions', 'status'])
            ->addIndexColumn()
            ->toJson();
    }

    private function dtDomainRegistrationFilters($criteria)
    {
        $filters = [];
        $pfx = $this->prefix;

        if (isset($criteria["clientname"])) {
            $filters[] = $this->filterValue("concat(firstname, ' ', lastname)", "LIKE", "'%{$criteria["clientname"]}%'"); 
        }

        if (isset($criteria["domain"])) {
            $filters[] = $this->filterValue("{$pfx}domains.domain", "LIKE", "'%{$criteria["domain"]}%'");
        }

        if (isset($criteria["statusdomain"]) && $criteria["statusdomain"] != "Any") {
            $filters[] = $this->filterValue("{$pfx}domains.status", "=", "'{$criteria["statusdomain"]}'");
        }

        if (isset($criteria["registrar"])) {
            $filters[] = $this->filterValue("{$pfx}domains.registrar", "=", "'{$criteria["registrar"]}'");
        }

        return $this->buildRawFilters($filters);
    }
    
    public function domainDetail(Request $request)
    {
        $domain = Domain::findOrFail($request->get("domainid"));
        $data = [];

        if (!$domain) {
            return ResponseAPI::Error([
                'message' => "Invalid ID",
            ]);
        }

        $data = [
            "fieldsordernum" => __("admin.fieldsordernum") .": " .($domain->orderid ? $domain->order->ordernum : ""),
            "fieldsregdate" => __("admin.fieldsregdate") .": " .Carbon::parse($domain->registrationdate)->toAdminDateFormat(),
            "fieldsordertype" => __("admin.ordersordertype") .": " .$domain->type, 
            "fieldsdnsmanagement" => __("admin.domainsdnsmanagement") .": " .($domain->dnsmanagement ? "Yes" : "No"), 
            "fieldsemailforwarding" => __("admin.domainsemailforwarding") .": " .($domain->emailforwarding ? "Yes" : "No"), 
            "fieldsidprotection" => __("admin.domainsidprotection") .": " .($domain->idprotection ? "Yes" : "No"),
            "fieldspremiumDomain" => __("admin.domainspremiumDomain") .": " .($domain->is_premium ? "Yes" : "No"),
            "fieldspaymentmethod" => __("admin.fieldspaymentmethod") .": " .($domain->paymentGateway()->name()->first()->value ?? "-"),
        ];

        return ResponseAPI::Success([
            'message' => "Success",
            'data' => $data,
        ]);
    }

    public function whois(Request $request)
    {
        $domain = $request->domain;
        $action = $request->action;
        $templatevars = [];

        if ($action == "checkavailability") {
            $whois = new WHOIS();
            $result = $whois->lookup(array("sld" => $request->sld, "tld" => $$request->tld));
            $templatevars["result"] = $result["result"];

            exit;
        }

        $resultArr = [];
        if ($domain) {
            $domains = new CoreDomains();
            $domainparts = $domains->splitAndCleanDomainInput($domain);
            $isValid = $domains->checkDomainisValid($domainparts);

            if ($isValid) {
                $whois = new WHOIS();
                if ($whois->canLookup($domainparts["tld"])) {
                    $result = $whois->lookup($domainparts);
                    if ($result["result"] == "available") {
                        $resultArr = [
                            "type" => "success",
                            "message" => sprintf(__("admin.whoisavailable"), $domain)
                        ];
                    } else {
                        if ($result["result"] == "unavailable") {
                            $resultArr = [
                                "type" => "danger",
                                "message" => sprintf(__("admin.whoisunavailable"), $domain)
                            ];
                        } else {
                            $resultArr = [
                                "type" => "danger",
                                "message" => __("admin.whoiserror") ."<br>" .$result["errordetail"]
                            ];
                        }
                    }
                } else {
                    $resultArr = [
                        "type" => "danger",
                        "message" => sprintf(__("admin.whoisinvalidtld"), $domainparts["tld"])
                    ];
                }
            } else {
                $resultArr = [
                    "type" => "danger",
                    "message" => __("admin.whoisinvaliddomain")
                ];
            }
        }

        $templatevars["domain"] = $domain;
        $templatevars["result"] = $resultArr;

        return view('pages.clients.domainregistrations.whois', $templatevars);
    }

    /*
    # TODO
    public function subscriptionInfo(\WHMCS\Http\Message\ServerRequest $request)
    {
        $relatedId = $request->get("id");
        try {
            $relatedItem = \WHMCS\Domain\Domain::findOrFail($relatedId);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Invalid Access Attempt");
        }
        return \WHMCS\Payment\Subscription::getInfo($relatedItem);
    }

    public function subscriptionCancel(\WHMCS\Http\Message\ServerRequest $request)
    {
        $relatedId = $request->get("id");
        try {
            $relatedItem = \WHMCS\Domain\Domain::findOrFail($relatedId);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Invalid Access Attempt");
        }
        return \WHMCS\Payment\Subscription::cancel($relatedItem);
    }
    */
}

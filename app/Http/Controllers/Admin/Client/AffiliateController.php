<?php

namespace App\Http\Controllers\Admin\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Rules\FloatValidator;
use Carbon\Carbon;
use Validator;

// Helpers
use App\Helpers\AdminFunctions;
use App\Helpers\CookieHelper;
use App\Helpers\Cfg;
use App\Helpers\Client as HelpersClient;
use App\Helpers\ClientHelper;
use App\Helpers\Format;
use App\Helpers\Functions;
use App\Helpers\Gateway;
use App\Helpers\Hooks;
use App\Helpers\Invoice;
use App\Helpers\LogActivity;
use App\Helpers\ResponseAPI;

// Models
use App\Models\Affiliate;
use App\Models\AffiliateAccount;
use App\Models\AffiliateHistory;
use App\Models\AffiliateHit;
use App\Models\AffiliatePending;
use App\Models\AffiliateReferrer;
use App\Models\AffiliateWithdrawal;
use App\Models\Client;
use App\Models\Credit;

// Traits
use App\Traits\DatatableFilter;

class AffiliateController extends Controller
{
    
    use DatatableFilter;

    public function __construct() {
        $this->middleware(['auth:admin'], ['except' => ['aff']]);
        $this->prefix = \Database::prefix();
    }

    public function index()
    {
        return view('pages.clients.manageaffiliates.index');
    }
    
    public function edit(Request $request)
    {
        $pfx = $this->prefix;
        $affiliates = Affiliate::findOrFail($request->id);
        $client = Client::findOrFail($affiliates->clientid);
        $affiliatesAccount = AffiliateAccount::where("affiliateid", $affiliates->id)->count();        
        $affiliatesPending = AffiliatePending::select(\DB::raw("COUNT(*) as pendingcommissions, SUM({$pfx}affiliatespending.amount) as pendingcommissionsamount"))
                                                    ->join("{$pfx}affiliatesaccounts", "{$pfx}affiliatesaccounts.id", "{$pfx}affiliatespending.affaccid")
                                                    ->join("{$pfx}hosting", "{$pfx}hosting.id", "{$pfx}affiliatesaccounts.relid")
                                                    ->join("{$pfx}products", "{$pfx}products.id", "{$pfx}hosting.packageid")
                                                    ->join("{$pfx}clients", "{$pfx}clients.id", "{$pfx}hosting.userid")
                                                    ->where("affiliateid", $affiliates->id)
                                                    ->orderBy("clearingdate", "DESC")
                                                    ->first();

        $affiliates->client_name = ClientHelper::outputClientLink($client->id, $client->firstname, $client->lastname, $client->companyname, $client->groupid);
        $affiliates->signups = $affiliatesAccount;
        $affiliates->pendingcommissions = $affiliatesPending->pendingcommissions;
        $affiliates->pendingcommissionsamount = Format::formatCurrency($affiliatesPending->pendingcommissionsamount);
        
        // Currency
        $affiliates->currency = Format::GetCurrency($affiliates->clientid);

        // Format date
        $dateFormat = (new HelpersClient())->getAdminDateFormat();
        $affiliates->date = Carbon::parse($affiliates->date)->format($dateFormat);
        
        // Conversionrate | throw devide by zero exception
        $affiliates->conversionrate = 0;
        try {
            $affiliates->conversionrate = round($affiliates->signups / $affiliates->visitors * 100, 2);
        } catch (\Throwable $th) {
            //throw $th;
        }

        $referralTimePeriods = [
            30 => "30 Days", 
            60 => "60 Days", 
            90 => "90 Days", 
            180 => "180 Days"
        ];

        $id = $request->id;
        $days = $request->get("days") ?? key($referralTimePeriods);
        $todayDate = Functions::getTodaysDate();
        $relatedReferrals = AffiliateAccount::selectRaw("{$pfx}affiliatesaccounts.*,(SELECT CONCAT({$pfx}clients.firstname," . "'|||',{$pfx}clients.lastname,'|||',{$pfx}hosting.userid,'|||',{$pfx}products.name,'|||'," . "{$pfx}hosting.domainstatus,'|||',{$pfx}hosting.domain,'|||',{$pfx}hosting.amount,'|||'," . "{$pfx}hosting.regdate,'|||',{$pfx}hosting.billingcycle) FROM {$pfx}hosting" . " INNER JOIN {$pfx}products ON {$pfx}products.id={$pfx}hosting.packageid" . " INNER JOIN {$pfx}clients ON {$pfx}clients.id={$pfx}hosting.userid " . "WHERE {$pfx}hosting.id={$pfx}affiliatesaccounts.relid) AS referraldata")
                    ->where("affiliateid", $id)
                    ->get();

        $paymentMethods = (new Gateway(request()))->paymentMethodsSelection( __("admin.na") );

        return view('pages.clients.manageaffiliates.edit', compact(
            'id', 
            'affiliates', 
            'referralTimePeriods',
            'days',
            'todayDate',
            'relatedReferrals',
            'paymentMethods'
        ));
    }

    public function manualPay(Request $request)
    {
        $id = $request->id;
        $pay = $request->pay;
        $affaccid = $request->affaccid;

        $type = $message = "";
        if ($pay) {
            $error = Functions::AffiliatePayment($affaccid, "");
            if ($error) {
                $type = 'danger';
                $message = AdminFunctions::infoBoxMessage('<b>Oh No!</b>', __("admin.affiiatespaymentfailed"));
            } else {
                $type = 'success';
                $message = AdminFunctions::infoBoxMessage( __("admin.affiiatespaymentsuccess"), __("admin.affiiatespaymentsuccessdetail"));
            }
        }

        return redirect()->route('admin.pages.clients.manageaffiliates.edit', ['id' => $id,])
                ->with('type', $type)
                ->with('message', $message);
    }

    public function update(Request $request) {
        $payTypes = ['percentage', 'fixed'];

        $validator = Validator::make($request->all(), [
            'id'   => "required|integer|exists:App\Models\Affiliate,id",
            'paytype'   => "nullable|string|in:".implode(",", $payTypes),
            'payamount'   => new FloatValidator(),
            'balance' => new FloatValidator(),
            'withdrawn' => new FloatValidator(),
            'visitors'   => "numeric",
            'onetime'   => "nullable|numeric",
        ]);

        if ($validator->fails()) {
            return redirect()
                    ->route('admin.pages.clients.manageaffiliates.edit', ['id' => $request->id])
                    ->withErrors($validator)
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> Please ensure to fill all fields correctly and re-submit the form.'));
        }

        $id = $request->id;

        $affiliates = Affiliate::findOrFail($id);
        $affiliates->paytype = $request->paytype;
        $affiliates->payamount = $request->payamount;
        $affiliates->onetime = $request->onetime;
        $affiliates->visitors = $request->visitors;
        $affiliates->balance = $request->balance;
        $affiliates->withdrawn = $request->withdrawn;
        $affiliates->save();

        LogActivity::Save("Affiliate ID $id Details Updated");

        return redirect()
                ->route('admin.pages.clients.manageaffiliates.edit', ['id' => $id])
                ->with('type', 'success')
                ->with('message', __('<b>Well Done!</b> The data has been successfully updated.'));
    }

    public function actionCommand(Request $request)
    {
        $action = $request->action;

        switch ($action) {
            case 'deletecommission':
                return $this->deletecommission($request);
            case 'deletehistory':
                return $this->deletehistory($request);
            case 'deletereferral':
                return $this->deletereferral($request);
            case 'deletewithdrawal':
                return $this->deletewithdrawal($request);
            case 'addcomm':
                return $this->addcomm($request);
            case 'withdraw':
                return $this->withdraw($request);
            case 'delete':
                return $this->delete($request);
            default:
                # code...
                break;
        }

        return abort(404, "Ups... Action not found!");
    }

    private function deletecommission(Request $request)
    {
        $id = $request->cid;

        $data = AffiliatePending::find($id);
        if (!$data) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', "Invalid ID."),
            ]);
        }

        $data->delete();
        return ResponseAPI::Success([
            'message' => "The data deleted successfully!",
        ]);
    }

    private function deletehistory(Request $request)
    {
        $id = $request->hid;

        $data = AffiliateHistory::find($id);
        if (!$data) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', "Invalid ID."),
            ]);
        }

        $data->delete();
        return ResponseAPI::Success([
            'message' => "The data deleted successfully!",
        ]);
    }

    private function deletereferral(Request $request)
    {
        $id = $request->affaccid;

        $data = AffiliateAccount::find($id);
        if (!$data) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', "Invalid ID."),
            ]);
        }

        $data->delete();
        return ResponseAPI::Success([
            'message' => "The data deleted successfully!",
        ]);
    }

    private function deletewithdrawal(Request $request)
    {
        $id = $request->wid;

        $data = AffiliateWithdrawal::find($id);
        if (!$data) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', "Invalid ID."),
            ]);
        }

        $data->delete();
        return ResponseAPI::Success([
            'message' => "The data deleted successfully!",
        ]);
    }

    private function addcomm(Request $request)
    {
        $id = $request->id;
        $aff = Affiliate::find((int) $id);
        if (!$aff) {
            return redirect()
                    ->route('admin.pages.clients.manageaffiliates.edit', ['id' => $id])
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> Invalid ID.'));
        }

        $amount = $request->amount;
        $amount = Functions::format_as_currency($amount);

        $affHis = new AffiliateHistory();
        $affHis->affiliateid = $request->id; 
        $affHis->date =  (new \App\Helpers\SystemHelper())->toMySQLDate($request->date); 
        // TODO: Check if null for refid or add validation
        $affHis->affaccid = $request->refid ?? 0; 
        $affHis->description = $request->description; 
        $affHis->amount = $amount;
        $affHis->save();

        // Update Affiliate
        $aff->increment("balance", $amount);
        $aff->save();

        return redirect()
                ->route('admin.pages.clients.manageaffiliates.edit', ['id' => $id])
                ->with('type', 'success')
                ->with('message', __('<b>Well Done!</b> The data saved successfully.'));
    }

    private function withdraw(Request $request)
    {
        $id = $request->id;
        $amount = $request->amount;
        // $amount = Format::format_as_currency($amount);

        $aff = Affiliate::find((int) $id);
        if (!$aff) {
            return redirect()
                    ->route('admin.pages.clients.manageaffiliates.edit', ['id' => $id])
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> Invalid ID.'));
        }

        $affWith = new AffiliateWithdrawal();
        $affWith->affiliateid = $id;
        $affWith->date = now();
        $affWith->amount = $amount;
        $affWith->save();

        // Update Affiliate
        $aff->decrement("balance", $amount);
        $aff->increment("withdrawn", $amount);
        $aff->save();

        $payouttype = $request->payouttype;
        $paymentmethod = $request->paymentmethod; 
        $transid = $request->transid;
        if ($payouttype == "1") {
            $id = (int) $aff->id;
            $clientid = (int) $aff->clientid;

            Invoice::addTransaction($clientid, "", "Affiliate Commissions Withdrawal Payout", "0", "0", $amount, $paymentmethod, $transid);
        } else if ($payouttype == "2") {
            $id = (int) $aff->id;
            $clientid = (int) $aff->clientid;
            
            $credit = new Credit();
            $credit->clientid = $clientid; 
            $credit->date = now(); 
            $credit->description = "Affiliate Commissions Withdrawal"; 
            $credit->amount = $amount;
            $credit->save();

            Client::find($clientid)->increment('credit', $amount);
            LogActivity::Save("Processed Affiliate Commissions Withdrawal to Credit Balance - User ID: $clientid - Amount: $amount");
        }

        return redirect()
                ->route('admin.pages.clients.manageaffiliates.edit', ['id' => $id])
                ->with('type', 'success')
                ->with('message', __('<b>Well Done!</b> Withdrawal Payout proccessed successfully.'));
    }

    public function delete(Request $request) {
        $data = Affiliate::findOrFail($request->id);

        if (!$data->delete()) {
            return ResponseAPI::Error([
                'message' => "Internal server error!",
            ]);
        }

        LogActivity::Save("Affiliate " .$request->id ." Deleted");
        
        return ResponseAPI::Success([
            'message' => "The data successfully deleted!",
        ]);
    }

    public function getChartData(Request $request)
    {
        $id = $request->id;
        $days = $request->days ?? 30;

        $chartData = [];
        $hitData = [];
        $referrers = \DB::table("{$this->prefix}affiliates_hits")
                        ->join("{$this->prefix}affiliates_referrers", "{$this->prefix}affiliates_referrers.id", "=", "{$this->prefix}affiliates_hits.referrer_id")
                        ->where("{$this->prefix}affiliates_hits.affiliate_id", "=", $id)
                        ->where("{$this->prefix}affiliates_hits.created_at", ">", Carbon::now()->subDays($days)->toDateTimeString())
                        ->groupBy(\DB::raw("date_format({$this->prefix}affiliates_hits.created_at, '%D %M %Y')"))
                        ->orderBy("{$this->prefix}affiliates_hits.created_at", "DESC")
                        ->selectRaw("{$this->prefix}affiliates_hits.created_at,COUNT({$this->prefix}affiliates_hits.id) as hits")
                        ->pluck("hits", "created_at");

        foreach ($referrers as $created => $referrer) {
            $hitData[substr($created, 0, 10)] = $referrer;
        }

        for ($chartDay = 1; $chartDay <= $days; $chartDay++) {
            $chartData["label"][] = Carbon::now()->subDays($days - $chartDay)->format("jS M Y");
            $chartData["value"][] = isset($hitData[Carbon::now()->subDays($days - $chartDay)->toDateString()]) ? $hitData[Carbon::now()->subDays($days - $chartDay)->toDateString()] : 0;

            // $chartData["rows"][] = [
            //     "c" => [
            //         ["v" => Carbon::now()->subDays($days - $chartDay)->format("jS F Y")], 
            //         ["v" => isset($hitData[Carbon::now()->subDays($days - $chartDay)->toDateString()]) ? $hitData[Carbon::now()->subDays($days - $chartDay)->toDateString()] : 0],
            //     ]
            // ];
        }

        // $chartData["cols"][] = ["label" => __("admin.fieldsdate"), "type" => "string"];
        // $chartData["cols"][] = ["label" => __("admin.affiiatesnumberOfHits"), "type" => "number"];
        $chartData["title"] = __("admin.affiiatesnumberOfHits");

        return ResponseAPI::Success([
            'message' => "OK!",
            'data' => $chartData,
        ]); 
    }

    public function dtAffiliates(Request $request) {
        $pfx = $this->prefix;
        $dataFiltered = $request->dataFiltered;

        $query = Affiliate::select(\DB::raw("
                                    {$pfx}affiliates.*,
                                    {$pfx}clients.firstname,
                                    {$pfx}clients.lastname,
                                    {$pfx}clients.companyname,
                                    {$pfx}clients.groupid,
                                    {$pfx}clients.currency,
                                    (SELECT COUNT(*) FROM {$pfx}affiliatesaccounts WHERE {$pfx}affiliatesaccounts.affiliateid={$pfx}affiliates.id) AS signups 
                                "))
                                ->where("{$pfx}affiliates.id", "!=", "")
                                ->join("{$pfx}clients", "{$pfx}clients.id", "{$pfx}affiliates.clientid");
                                
        $filters = $this->dtAffiliatesFilters($dataFiltered);
        if ($filters) {
            $query->whereRaw($filters);
        }

        return datatables()->of($query)->addColumn('raw_id', function($row) {
                    $route = route('admin.pages.clients.manageaffiliates.edit', ['id' => $row->id]);

                    return "<a href=\"{$route}\">{$row->id}</a>";
                })
                ->editColumn('currency', function($row) {
                    return \App\Helpers\Format::GetCurrency("", $row->currency);
                })
                ->editColumn('balance', function($row) {
                    return \App\Helpers\Format::formatCurrency($row->balance);
                })
                ->editColumn('withdrawn', function($row) {
                    return \App\Helpers\Format::formatCurrency($row->withdrawn);
                })
                ->editColumn('date', function($row) {
                    // return (new \App\Helpers\Client())->fromMySQLDate($row->date);
                    $dateFormat = (new \App\Helpers\Client())->getAdminDateFormat();

                    return \Carbon\Carbon::parse($row->date)->format($dateFormat);
                })
                ->addColumn('full_name', function($row) {
                    $link = ClientHelper::outputClientLink($row->clientid, $row->firstname, $row->lastname, $row->companyname, $row->groupid);

                    return $link;
                })
                ->addColumn('actions', function($row) {
                    $route = route('admin.pages.clients.manageaffiliates.edit', ['id' => $row->id]);
                    $action = "";

                    $action .= "<a href=\"{$route}\" type=\"button\" class=\"btn btn-xs text-primary p-1 act-edit\" data-id=\"{$row->id}\" title=\"Edit\"><i class=\"fa fa-edit\"></i></a> ";
                    $action .= "<button type=\"button\" class=\"btn btn-xs text-danger p-1 act-delete\" data-id=\"{$row->id}\" title=\"Delete\"><i class=\"fa fa-trash\"></i></button> ";

                    return $action;
                })
                ->orderColumn('raw_id', function($query, $order) {
                    $query->orderBy('id', $order);
                })
                ->rawColumns(['raw_id', 'full_name', 'actions'])
                ->addIndexColumn()
                ->toJson();
    }

    private function dtAffiliatesFilters($criteria)
    {
        $filters = [];

        if (isset($criteria["client"])) {
            $filters[] = $this->filterValue("concat(firstname,' ',lastname)", "LIKE", "'%{$criteria["client"]}%'");
        }

        if (isset($criteria["visitors"])) {
            $filters[] = $this->filterValue("visitors", $criteria["visitorsType"], "'{$criteria["visitors"]}'");
        }

        if (isset($criteria["balance"])) {
            $filters[] = $this->filterValue("balance", $criteria["balanceType"], "'{$criteria["balance"]}'");
        }

        if (isset($criteria["withdrawn"])) {
            $filters[] = $this->filterValue("withdrawn", $criteria["withdrawnType"], "'{$criteria["withdrawn"]}'");
        }

        return $this->buildRawFilters($filters);
    }

    public function dtReferrals(Request $request) {
        $pfx = $this->prefix;
        $dataFiltered = $request->dataFiltered;
        
        $id = $dataFiltered["id"];
        $days = $dataFiltered["days"];

        $query = AffiliateHit::selectRaw("referrer,COUNT({$pfx}affiliates_hits.id) as hits")
                    ->join("{$pfx}affiliates_referrers", "{$pfx}affiliates_referrers.id", "=", "{$pfx}affiliates_hits.referrer_id")
                    ->where("{$pfx}affiliates_hits.affiliate_id", "=", $id)
                    ->where("{$pfx}affiliates_hits.created_at", ">", Carbon::now()->subDays($days)->toDateTimeString())
                    ->groupBy("{$pfx}affiliates_hits.referrer_id")
                    ->orderBy("hits", "DESC");
                    // ->pluck("hits", "referrer");

        return datatables()->of($query)
                ->editColumn('referrer', function($row) {
                    $referrer = $row->referrer;

                    if (!trim($referrer)) {
                        $referrer = __("admin.affiiatesnoReferrer");
                    } else if (120 < strlen($referrer)) {
                        $referrer = substr($referrer, 0, 120) . "... <a href=\"#\">Reveal</a>";
                    }

                    return $referrer;
                })
                ->editColumn('hits', function($row) {
                    return $row->hits;
                })
                ->orderColumn('referrer', function($query, $order) {
                    $query->orderBy('referrer', $order);
                })
                ->rawColumns(['referrer'])
                ->addIndexColumn()
                ->toJson();
    }

    public function dtReferredSignups(Request $request) {
        $pfx = $this->prefix;
        $dataFiltered = $request->dataFiltered;
        
        $id = $dataFiltered["id"];
        
        $params = [
            "id" => $id,
            "pfx" => $pfx,
        ];

        $query = AffiliateAccount::selectRaw("{$pfx}affiliatesaccounts.id,{$pfx}affiliatesaccounts.lastpaid,{$pfx}affiliatesaccounts.relid, concat({$pfx}clients.firstname,' ',{$pfx}clients.lastname,'|||',{$pfx}clients.currency) as clientname,{$pfx}products.name,{$pfx}hosting.userid,{$pfx}hosting.domainstatus,{$pfx}hosting.domain,{$pfx}hosting.amount,{$pfx}hosting.firstpaymentamount,{$pfx}hosting.regdate,{$pfx}hosting.billingcycle")
                    ->where("{$pfx}affiliatesaccounts.affiliateid", $id)
                    ->join("{$pfx}hosting", "{$pfx}hosting.id", "{$pfx}affiliatesaccounts.relid")
                    ->join("{$pfx}products", "{$pfx}products.id", "{$pfx}hosting.packageid")
                    ->join("{$pfx}clients", "{$pfx}clients.id", "{$pfx}hosting.userid");

        return datatables()->of($query)
                ->addColumn('affaccid', function($row) {
                   return $row->id;
                })
                ->addColumn('date', function($row) {
                    return (new \App\Helpers\Client())->fromMySQLDate($row->regdate);
                })
                ->addColumn('client', function($row) {
                    $route = route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $row->userid]);
                    $clientname = $row->clientname;
                    $clientname = explode("|||", $clientname, 2);
                    list($clientname, $referralCurrency) = $clientname;

                    return "<a href=\"{$route}\">{$clientname}</a>";
                })
                ->addColumn('productservice', function($row) {
                    $route = route('admin.pages.clients.viewclients.clientservices.index', [
                        'userid' => $row->userid,
                        'id' => $row->relid
                    ]);

                    $product = $row->name;
                    $billingcycle = $row->billingcycle;
                    $firstpaymentamount = $row->firstpaymentamount;
                    $amount = $row->amount;

                    if ($billingcycle == "Free" || $billingcycle == "Free Account") {
                        $amountdesc = "Free";
                    } else if ($billingcycle == "One Time") {
                        $amountdesc = Format::formatCurrency($firstpaymentamount) . " " . $billingcycle;
                    } else {
                        $amountdesc = $firstpaymentamount != $amount ? Format::formatCurrency($firstpaymentamount) . " " . __("admin.affiiatesinitiallythen") . " " : "";
                        $amountdesc .= Format::formatCurrency($amount) . " " . $billingcycle;
                    }

                    return "<a href=\"{$route}\">{$product}</a><br>$amountdesc";
                })
                ->addColumn('commission', function($row) use($params) {
                    $id = $params["id"];
                    $relid = $row->relid; 
                    $lastpaid = $row->lastpaid;

                    $commission = Functions::calculateAffiliateCommission($id, $relid, $lastpaid);
                    $commission = Format::formatCurrency($commission);

                    return $commission;
                })
                ->editColumn('lastpaid', function($row) {
                    $lastpaid = $row->lastpaid;

                    if ($lastpaid == "0000-00-00") {
                        $lastpaid = __("admin.affiiatesnever");
                    } else {
                        $lastpaid = (new \App\Helpers\Client())->fromMySQLDate($lastpaid);
                    }

                    return $lastpaid;
                })
                ->addColumn('status', function($row) {
                   return $row->domainstatus;
                })
                ->addColumn('actions', function($row) use($params) {
                    $id = $params["id"];
                    $affaccid = $row->id;

                    // TODO: Check for what pay params 
                    $route = route('admin.pages.clients.manageaffiliates.manualpay', [
                        'id' => $id,
                        'pay' => true,
                        'affaccid' => $affaccid,
                    ]);
                    $action = "";

                    $action .= "<a href=\"{$route}\" type=\"button\" class=\"btn btn-xs text-primary p-1 act-payout\" data-id=\"{$affaccid}\" title=\"Payout\">" . __("admin.affiiatesmanual") . " " . __("admin.affiiatespayout") ."</a> <br>";
                    $action .= "<button type=\"button\" class=\"btn btn-xs text-danger p-1 act-delete\" data-id=\"{$affaccid}\" title=\"Delete\" onclick=\"actionCommand('deletereferral', this)\"><i class=\"fa fa-trash\"></i></button> ";

                    return $action;
                })
                ->orderColumn('affaccid', function($query, $order) {
                    $query->orderBy('id', $order);
                })
                ->rawColumns(['client', 'productservice', 'actions'])
                ->addIndexColumn()
                ->toJson();
    }

    public function dtPendingCommissions(Request $request) {
        $pfx = $this->prefix;
        $dataFiltered = $request->dataFiltered;
        
        $id = $dataFiltered["id"];
        
        $query = AffiliatePending::selectRaw("{$pfx}affiliatespending.id,{$pfx}affiliatespending.affaccid,{$pfx}affiliatespending.amount,{$pfx}affiliatespending.clearingdate,{$pfx}affiliatesaccounts.relid,{$pfx}clients.firstname,{$pfx}clients.lastname,{$pfx}clients.companyname,{$pfx}products.name,{$pfx}hosting.userid,{$pfx}hosting.domainstatus,{$pfx}hosting.billingcycle")
                    ->where("affiliateid", $id)
                    ->join("{$pfx}affiliatesaccounts", "{$pfx}affiliatesaccounts.id", "{$pfx}affiliatespending.affaccid")
                    ->join("{$pfx}hosting", "{$pfx}hosting.id", "{$pfx}affiliatesaccounts.relid")
                    ->join("{$pfx}products", "{$pfx}products.id", "{$pfx}hosting.packageid")
                    ->join("{$pfx}clients", "{$pfx}clients.id", "{$pfx}hosting.userid");

        return datatables()->of($query)
                ->editColumn('affaccid', function($row) {
                    return $row->affaccid;
                })
                ->addColumn('client', function($row) {
                    $link = ClientHelper::outputClientLink($row->userid, $row->firstname, $row->lastname, $row->companyname);

                    return $link;
                })
                ->addColumn('productservice', function($row) {
                    $route = route('admin.pages.clients.viewclients.clientservices.index', [
                        'userid' => $row->userid,
                        'id' => $row->relid
                    ]);

                    $product = $row->name;

                    return "<a href=\"{$route}\">{$product}</a>";
                })
                ->addColumn('status', function($row) {
                    return $row->domainstatus;
                })
                ->editColumn('amount', function($row) {
                    return Format::formatCurrency($row->amount);
                })
                ->editColumn('clearingdate', function($row) {
                    return (new \App\Helpers\Client())->fromMySQLDate($row->clearingdate);
                })
                ->addColumn('actions', function($row) {
                    $pendingid = $row->id;
                    $action = "";

                    $action .= "<button type=\"button\" class=\"btn btn-xs text-danger p-1 act-delete\" data-id=\"{$pendingid}\" title=\"Delete\" onclick=\"actionCommand('deletecommission', this)\"><i class=\"fa fa-trash\"></i></button> ";

                    return $action;
                })
                ->orderColumn('productservice', function($query, $order) {
                    $query->orderBy('relid', $order);
                })
                ->orderColumn('status', function($query, $order) {
                    $query->orderBy('domainstatus', $order);
                })
                ->orderColumn('client', function($query, $order) {
                    $query->orderBy('firstname', $order);
                })
                ->rawColumns(['client', 'productservice', 'actions'])
                ->addIndexColumn()
                ->toJson();
    }

    public function dtCommissionsHistory(Request $request) {
        $pfx = $this->prefix;
        $dataFiltered = $request->dataFiltered;
        
        $id = $dataFiltered["id"];

        $query = AffiliateHistory::selectRaw("{$pfx}affiliateshistory.*,(SELECT CONCAT({$pfx}clients.id,'|||',{$pfx}clients.firstname,'|||',{$pfx}clients.lastname,'|||',{$pfx}clients.companyname,'|||',{$pfx}products.name,'|||',{$pfx}hosting.id,'|||',{$pfx}hosting.billingcycle,'|||',{$pfx}hosting.domainstatus) FROM {$pfx}affiliatesaccounts INNER JOIN {$pfx}hosting ON {$pfx}hosting.id={$pfx}affiliatesaccounts.relid INNER JOIN {$pfx}products ON {$pfx}products.id={$pfx}hosting.packageid INNER JOIN {$pfx}clients ON {$pfx}clients.id={$pfx}hosting.userid WHERE {$pfx}affiliatesaccounts.id={$pfx}affiliateshistory.affaccid) AS referraldata")
                    ->where("affiliateid", $id);

        return datatables()->of($query)
                ->editColumn('date', function($row) {
                    return (new \App\Helpers\Client())->fromMySQLDate($row->date);
                })
                ->editColumn('affaccid', function($row) {
                    return $row->affaccid;
                })
                ->addColumn('client', function($row) {
                    $affaccid = $row->affaccid;
                    $referraldata = $row->referraldata;

                    extract($this->generateReferraldata($affaccid, $referraldata));
                    return ClientHelper::outputClientLink($userid, $firstname, $lastname, $companyname);
                })
                ->addColumn('productservice', function($row) {
                    $affaccid = $row->affaccid;
                    $referraldata = $row->referraldata;

                    extract($this->generateReferraldata($affaccid, $referraldata));
                    $route = route('admin.pages.clients.viewclients.clientservices.index', [
                        'userid' => $userid,
                        'id' => $relid
                    ]);

                    return "<a href=\"{$route}\">{$product}</a>";
                })
                ->addColumn('status', function($row) {
                    $affaccid = $row->affaccid;
                    $referraldata = $row->referraldata;

                    extract($this->generateReferraldata($affaccid, $referraldata));

                    return $status;
                })
                ->editColumn('description', function($row) {
                    return $row->description;
                })
                ->editColumn('amount', function($row) {
                    return Format::formatCurrency($row->amount);
                })
                ->addColumn('actions', function($row) {
                    $historyid = $row->id;
                    $action = "";

                    $action .= "<button type=\"button\" class=\"btn btn-xs text-danger p-1 act-delete\" data-id=\"{$historyid}\" title=\"Delete\" onclick=\"actionCommand('deletehistory', this)\"><i class=\"fa fa-trash\"></i></button> ";

                    return $action;
                })
                ->orderColumn('productservice', function($query, $order) {
                    $query->orderBy('affaccid', $order);
                })
                ->orderColumn('status', function($query, $order) {
                    $query->orderBy('affaccid', $order);
                })
                // ->orderColumn('client', function($query, $order) use($pfx) {
                //     $query->orderBy("affaccid", $order);
                // })
                ->rawColumns(['client', 'productservice', 'actions'])
                ->addIndexColumn()
                ->toJson();
    }

    public function dtWithdrawalsHistory(Request $request) {
        $pfx = $this->prefix;
        $dataFiltered = $request->dataFiltered;
        
        $id = $dataFiltered["id"];

        $query = AffiliateWithdrawal::where("affiliateid", $id);

        return datatables()->of($query)
                ->editColumn('date', function($row) {
                    return (new \App\Helpers\Client())->fromMySQLDate($row->date);
                })
                ->editColumn('amount', function($row) {
                    return Format::formatCurrency($row->amount);
                })
                ->addColumn('actions', function($row) {
                    $historyid = $row->id;
                    $action = "";

                    $action .= "<button type=\"button\" class=\"btn btn-xs text-danger p-1 act-delete\" data-id=\"{$historyid}\" title=\"Delete\" onclick=\"actionCommand('deletewithdrawal', this)\"><i class=\"fa fa-trash\"></i></button> ";

                    return $action;
                })
                ->rawColumns(['actions'])
                ->addIndexColumn()
                ->toJson();
    }

    private function generateReferraldata($affaccid, $referraldata)
    {
        $referraldata = explode("|||", $referraldata);
        $userid = $firstname = $lastname = $companyname = $product = $relid = $billingcycle = $status = "";

        if ($affaccid) {
            list($userid, $firstname, $lastname, $companyname, $product, $relid, $billingcycle, $status) = $referraldata;            
        }

        return compact("userid", "firstname", "lastname", "companyname", "product", "relid", "billingcycle", "status");
    }

    // NOTE: aff function should be in client area 
    public function aff(Request $request)
    {
        $aff = $request->get('aff');
        $affData = Affiliate::find($aff);

        if (!$aff && !$affData) {
            return abort(404, "Page not found!");
        }

        // if affiliate id is present, update visitor count & set cookie
        $affData->increment("visitors", 1);
        CookieHelper::set('AffiliateID', $aff, '3m');

        // TODO: get the referal URL
        // \URL::previous(); or url()->previous(); ??
        $referrer = trim(\Request::server('HTTP_REFERER'));

        AffiliateReferrer::firstOrCreate([
            'affiliate_id' => $aff,
            'referrer' => $referrer,
        ])->hits()->create([
            'affiliate_id' => $aff,
            'created_at' => Carbon::now()->toDateTimeString(),
        ]);

        /**
         * Executes when a user has clicked an affiliate referral link.
         *
         * @param int $affiliateId The unique id of the affiliate that the link belongs to
         */
        Hooks::run_hook("AffiliateClickthru", array(
            'affiliateId' => $aff,
        ));

        // TODO: Custom redirect if custom params exist but it's required cart.php page
        // // if product id passed in, redirect to order form
        // if ($pid = $request->get('pid')) redir("a=add&pid=".(int)$pid,"cart.php");

        // // if product group id passed in, redirect to product group
        // if ($gid = $request->get('gid')) redir("gid=".(int)$gid,"cart.php");

        // // if register = true, redirect to registration form
        // if ($request->get('register')) redir("","register.php");

        // // if gocart = true, redirect to cart with request params
        // if ($request->get('gocart')) {
        //     $reqvars = '';
        //     foreach ($_GET AS $k=>$v) $reqvars .= $k.'='.urlencode($v).'&';
        //     redir($reqvars,"cart.php");
        // }

        // perform default redirect
        // header("HTTP/1.1 301 Moved Permanently");
        // header("Location: ".Cfg::get('Domain'), true, 301);

        return \Redirect::to(Cfg::get('Domain'));
    }
    
}

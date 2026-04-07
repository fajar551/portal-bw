<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Affiliate;
use App\Models\AffiliateAccount;
use App\Models\AffiliatePending;

use App\Helpers\Cfg;
use App\Helpers\Affiliate as HelpersAffiliate;
use App\Helpers\Carbon;
use App\Helpers\Client;
use App\Helpers\Format;
use App\Helpers\Hooks;
use App\Helpers\Ticket;
use App\Models\Ticketdepartment;
use Illuminate\Support\Facades\Lang;
use DB;
use Auth;


class AffiliateController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:web']);
        $this->prefix = \Database::prefix();
    }

    public function Affiliate()
    {
        $auth = Auth::guard('web')->user();
        $userId = $auth->id;
        $activateTitle = __('client.affiliatesactivate');
        $affiliatesTitle = __('client.affiliatestitle');
        $pfx = $this->prefix;


        if ($userId) {
            $result = Affiliate::where('clientid', $userId)->first();
            // dd($result);
            $clientid = $result ? $result->clientid : 0;
            $dateFormat = (new Client())->getAdminDateFormat();
            $affiliatesAccount = AffiliateAccount::where("affiliateid", $clientid)->count();
            $affiliatesPending = AffiliatePending::select(\DB::raw("COUNT(*) as pendingcommissions, SUM({$pfx}affiliatespending.amount) as pendingcommissionsamount"))
                ->join("{$pfx}affiliatesaccounts", "{$pfx}affiliatesaccounts.id", "{$pfx}affiliatespending.affaccid")
                ->join("{$pfx}hosting", "{$pfx}hosting.id", "{$pfx}affiliatesaccounts.relid")
                ->join("{$pfx}products", "{$pfx}products.id", "{$pfx}hosting.packageid")
                ->join("{$pfx}clients", "{$pfx}clients.id", "{$pfx}hosting.userid")
                ->where("affiliateid", $clientid)
                ->orderBy("clearingdate", "DESC")
                ->first();
            $params = array();
            if (!$result) {
                $params['checkAffAccount'] = $result;
                $params['activateTitle'] = $activateTitle;
                $params['affiliatesTitle'] = $affiliatesTitle;
                return view('pages.affiliate.index', ['params' => $params]);
            } else {
                $affiliatesAccount = AffiliateAccount::where("affiliateid", $result->id)->count() ?? 0;
                // dd($affiliatesAccount);
                $affiliatesPending = AffiliatePending::select(\DB::raw("COUNT(*) as pendingcommissions, SUM({$pfx}affiliatespending.amount) as pendingcommissionsamount"))
                    ->join("{$pfx}affiliatesaccounts", "{$pfx}affiliatesaccounts.id", "{$pfx}affiliatespending.affaccid")
                    ->join("{$pfx}hosting", "{$pfx}hosting.id", "{$pfx}affiliatesaccounts.relid")
                    ->join("{$pfx}products", "{$pfx}products.id", "{$pfx}hosting.packageid")
                    ->join("{$pfx}clients", "{$pfx}clients.id", "{$pfx}hosting.userid")
                    ->where("affiliateid", $result->id)
                    ->orderBy("clearingdate", "DESC")
                    ->first();
                $params['commissionpending'] = $affiliatesPending->pendingcommissions;
                $params['pendingcommissionsamount'] = Format::Price($affiliatesPending->pendingcommissionsamount, true);
                $params['currency'] = Format::GetCurrency($result->clientid);
                $params['date'] = Carbon::parse($result->date)->format($dateFormat);
                $params['signups'] = $affiliatesAccount;
                $params['visitors'] = $result->visitors;
                $params['withdraw'] = $result->withdrawn;
                $params['payamount'] = Format::formatCurrency($result->payamount);
                $params['checkAffAccount'] = $result;
                $params['activateTitle'] = $activateTitle;
                $params['affiliatesTitle'] = $affiliatesTitle;
                $params['success'] = "Your Affiliate Account is Active";
                $params['referrallink'] = Cfg::get('SystemURL') . "aff.php?aff=" . $result->id;
                $params['affpayoutmin'] = Format::formatCurrency(Cfg::get('AffiliatePayout'));
                $conversionrate = 0 < $result->visitors ? round($affiliatesAccount / $result->visitors * 100, 2) : 0;
                $params['conversionrate'] = $conversionrate;
                return view('pages.affiliate.index', ['params' => $params]);
            }
        }
    }

    public function ActivateAffiliateAccount(Request $request)
    {
        $auth = Auth::guard('web')->user();
        $userId = $auth->id;
        $activate = $request->activate;

        if ($userId && $activate) {
            $result = Affiliate::where('clientid', $userId)->first();
            $affiliateSetting = Cfg::get('AffiliateEnabled');
            if (!$result && $affiliateSetting == "on") {
                HelpersAffiliate::Activate($userId);
                return redirect()->route('pages.affiliate.index')->with('success', 'Your Affiliate Account is Active');
            }
        }
    }

    public function WithdrawRequest(Request $request)
    {
        $affpayoutmin = $request->affpayoutmin;
        $auth = Auth::guard('web')->user();
        $userId = $auth->id;
        $aff = Affiliate::where('clientid', $userId)->first();
        $balance = $aff['balance'];
        $affId = $aff['id'];
        $deptid = "";
        $amountUntilWithdrawal = $affpayoutmin - $balance;

        if ($affpayoutmin <= $balance) {
            if (Cfg::get('AffiliateDepartment')) {
                $getdeptid = Ticketdepartment::select('id')->where('id', Cfg::get('AffiliateDepartment'))->first();
                $deptid = $getdeptid['id'];
            }
            if (!$deptid) {
                $getdeptid = Ticketdepartment::select('id')->where('hidden', '')->orderBy('id', 'asc')->first();
                $deptid = $getdeptid['id'];
            }

            $message = "Affiliate Account Withdrawal Request.  Details below:\n\nClient ID: " . $userId . "\nAffiliate ID: " . $affId . "\nBalance: " . $balance;
            $responses = Hooks::run_hook('AffiliateWithdrawalRequest', array("affiliateId" => $affId, "userId" => $userId, "balance" => $balance));

            $skipTicket = false;
            foreach ($responses as $response) {
                if (array_key_exists("skipTicket", $response) && $response["skipTicket"]) {
                    $skipTicket = true;
                }
            }
            if (!$skipTicket) {
                $from = [
                    'name' => $auth->firstname,
                    'email' => $auth->email
                ];

                Ticket::OpenNewTicket($userId, "", $deptid, "Affiliate Withdrawal Request", $message, "Medium", "", $from, "", "", "", false);
                return redirect()->route('pages.affiliate.index')->with('success', __('client.affiliateswithdrawalrequestsuccessful'));
            }
        } else {
            if (0 < $amountUntilWithdrawal) {
                $msgTemplate = __('client.clientHomePanelsaffiliateSummary');
            } else {
                $msgTemplate = __('client.affiliateswithdrawalrequestsuccessful');
            }
            $msg = Lang::get($msgTemplate, array("commissionBalance" => $balance, "amountUntilWithdrawalLevel" => Format::formatCurrency($amountUntilWithdrawal)));

            return redirect()->back()->with('error_withdraw', $msg);
        }
    }

    public function dtAffiliate() {
        $auth = Auth::guard('web')->user();
        $userid = $auth->id;
        $result = Affiliate::where('clientid', $userid)->first();
        $affiliateId = $result->id;
        // $pfx = $this->prefix;
        $firstOfLastMonth = mktime(0, 0, 0, date("m") - 1, date("d"), date("Y"));
        $lastOfLastMonth = mktime(23, 59, 59, date("m"), date("d") - 1, date("Y"));

        // $numitems = Affiliatesaccount::select(\DB::raw("COUNT(*)", array("affiliateid" => $affiliateId), "", "", "", "{$pfx}hosting ON {$pfx}hosting.id = {$pfx}affiliatesacconts.relid INNER JOIN {$pfx}products ON {$pfx}products.id = {$pfx}hosting.packageid INNER JOIN {$pfx}clients ON {$pfx}clients.id={$pfx}hosting.userid"))->first();

        $affiliatesAccounts = DB::table("tblaffiliatesaccounts")->where("affiliateid", "=", $affiliateId)->join("tblhosting", "tblhosting.id", "=", "tblaffiliatesaccounts.relid")->join("tblproducts", "tblproducts.id", "=", "tblhosting.packageid")->join("tblclients", "tblclients.id", "=", "tblhosting.userid")->orderBy("regdate", "DESC")->get(array("tblaffiliatesaccounts.*", "tblproducts.name", "tblhosting.packageid", "tblhosting.userid", "tblhosting.domainstatus", "tblhosting.amount", "tblhosting.firstpaymentamount", "tblhosting.regdate", "tblhosting.billingcycle"));

        // dd($affiliatesAccounts);
        return datatables()->of($affiliatesAccounts)->editColumn("regdate", function ($row) {
            return $row->regdate;
        })
        ->editColumn('name', function ($row) {
            return $row->name;
        })
        ->editColumn('amount', function ($row) {
            return Format::formatCurrency($row->amount);
        })
        ->editColumn('billingcycle', function ($row) {
            return $row->billingcycle;
        })
        ->editColumn('commision', function ($row) use($affiliateId) {
            $commission = \App\Helpers\Invoice::calculateAffiliateCommission($affiliateId, $row->relid);
            return $commission;
        })
        ->editColumn('domainstatus', function ($row) {
            $hostingStatus = $row->domainstatus;
            $hostingStatus = \Lang::get("clientarea" . strtolower($hostingStatus));
            return __('client.'.$hostingStatus);
        })
        ->addIndexColumn()
        ->toJson();
    }
}

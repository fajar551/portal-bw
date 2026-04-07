<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticketdepartment;
use Spatie\Permission\Models\Role;
use API;
use App\Helpers\Password;
use App\Helpers\Sanitize;
use App\Models\Admin;
use App\Helpers\Database;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $prefix;

    public function __construct()
    {
        $this->middleware(['auth:admin']);
        $this->prefix = Database::prefix();
    }

    public function Dashboard()
    {
        $users = Auth::guard('admin')->user();
        if (!$users) {
            return redirect()->route('admin.login')->with('message', 'Please login first, try again.');
        }

        // Check permission first before processing data
        if (!\App\Helpers\AdminFunctions::checkPermission("Main Homepage", true)) {
            // Return error view with layout instead of throwing exception
            return view('pages.dashboard.index', [
                'error' => 'You don\'t have permission to Main Homepage',
                'getPendingOrder' => 0,
                'getSupportAwaitingReply' => 0,
                'getCancellationRequest' => 0,
                'getSupportAssignedToYou' => 0,
                'orderAllStatusDataString' => '',
                'orderActiveStatusDataString' => '',
                'totalPaidInvoicesDataString' => '',
                'months' => collect(),
                'days' => [],
                'orderAllStatusLastMonth' => json_encode([]),
                'orderActiveStatusLastMonth' => json_encode([]),
                'totalPaidInvoicesLastMonth' => json_encode([]),
                'orderAllStatusToday' => json_encode([]),
                'orderActiveStatusToday' => json_encode([]),
                'totalPaidInvoicesToday' => json_encode([]),
                'billingToday' => 0,
                'billingThisMonth' => 0,
                'billingThisYear' => 0,
                'billingAllTime' => 0,
                'tickets' => collect(),
                'adminList' => collect(),
                'activities' => collect(),
                'activeClientsCount' => 0,
                'usersOnlineCount' => 0,
                'clients' => collect(),
            ]);
        }

        $getPendingOrder = DB::table('tblorders')->where('status', 'pending')->count();
        $getSupportAwaitingReply = DB::table('tbltickets')->where('status', 'open')->count();
        $getCancellationRequest = DB::table('tblcancelrequests')->count();

        // Get the admin name
        $adminName = \App\Helpers\AdminFunctions::getAdminName();

        // Get the count of tickets assigned to the logged-in admin
        $getSupportAssignedToYou = DB::table('tbltickets')
            ->where('status', 'open')
            ->where('admin', $adminName)
            ->count();

        // Get the last 12 months
        $months = collect();
        for ($i = 0; $i < 12; $i++) {
            $months->push(Carbon::now()->subMonths($i)->format('Y-m'));
        }
        $months = $months->reverse()->values();

        // Initialize data arrays with zeros
        $orderAllStatusData = array_fill(0, 12, 0);
        $orderActiveStatusData = array_fill(0, 12, 0);
        $totalPaidInvoicesData = array_fill(0, 12, 0);

        // Fetch data for the chart
        $orderAllStatus = DB::table('tblorders')
            ->selectRaw('YEAR(date) as year, MONTH(date) as month, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        $orderActiveStatus = DB::table('tblorders')
            ->selectRaw('YEAR(date) as year, MONTH(date) as month, COUNT(*) as count')
            ->where('status', 'Active')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        $totalPaidInvoices = DB::table('tblinvoices')
            ->selectRaw('YEAR(date) as year, MONTH(date) as month, COUNT(*) as count')
            ->where('status', 'Paid')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Update data arrays with actual counts
        foreach ($months as $index => $month) {
            $yearMonth = explode('-', $month);
            $year = $yearMonth[0];
            $month = $yearMonth[1];

            $orderAllStatusData[$index] = $orderAllStatus->first(function ($value) use ($year, $month) {
                return $value->year == $year && $value->month == $month;
            })->count ?? 0;

            $orderActiveStatusData[$index] = $orderActiveStatus->first(function ($value) use ($year, $month) {
                return $value->year == $year && $value->month == $month;
            })->count ?? 0;

            $totalPaidInvoicesData[$index] = $totalPaidInvoices->first(function ($value) use ($year, $month) {
                return $value->year == $year && $value->month == $month;
            })->count ?? 0;
        }

        // New logic for last 30 days and today
        $today = Carbon::today();
        $thirtyDaysAgo = Carbon::today()->subDays(30);

        // Last 30 Days Data (Daily)
        $orderAllStatusLastMonth = [];
        $orderActiveStatusLastMonth = [];
        $totalPaidInvoicesLastMonth = [];
        $days = [];

        for ($date = $thirtyDaysAgo; $date <= $today; $date->addDay()) {
            $days[] = $date->format('Y-m-d');
            $orderAllStatusLastMonth[] = DB::table('tblorders')
                ->whereDate('date', $date)
                ->count();

            $orderActiveStatusLastMonth[] = DB::table('tblorders')
                ->where('status', 'Active')
                ->whereDate('date', $date)
                ->count();

            $totalPaidInvoicesLastMonth[] = DB::table('tblinvoices')
                ->where('status', 'Paid')
                ->whereDate('date', $date)
                ->count();
        }

        // Today Data (Hourly)
        $orderAllStatusToday = [];
        $orderActiveStatusToday = [];
        $totalPaidInvoicesToday = [];

        for ($hour = 0; $hour < 24; $hour++) {
            $orderAllStatusToday[] = DB::table('tblorders')
                ->whereDate('date', $today)
                ->whereTime('date', '>=', $hour . ':00:00')
                ->whereTime('date', '<', ($hour + 1) . ':00:00')
                ->count();

            $orderActiveStatusToday[] = DB::table('tblorders')
                ->where('status', 'Active')
                ->whereDate('date', $today)
                ->whereTime('date', '>=', $hour . ':00:00')
                ->whereTime('date', '<', ($hour + 1) . ':00:00')
                ->count();

            $totalPaidInvoicesToday[] = DB::table('tblinvoices')
                ->where('status', 'Paid')
                ->whereDate('date', $today)
                ->whereTime('date', '>=', $hour . ':00:00')
                ->whereTime('date', '<', ($hour + 1) . ':00:00')
                ->count();
        }

        // Billing calculations
        $billingToday = DB::table('tblinvoices')
            ->where('status', 'Paid')
            ->whereDate('date', $today)
            ->sum('total');

        $billingThisMonth = DB::table('tblinvoices')
            ->where('status', 'Paid')
            ->whereYear('date', $today->year)
            ->whereMonth('date', $today->month)
            ->sum('total');

        $billingThisYear = DB::table('tblinvoices')
            ->where('status', 'Paid')
            ->whereYear('date', $today->year)
            ->sum('total');

        $billingAllTime = DB::table('tblinvoices')
            ->where('status', 'Paid')
            ->sum('total');

        // Fetch ticket data
        $tickets = DB::table('tbltickets')
            ->select(
                'tbltickets.tid',
                DB::raw('IF(tbltickets.name = "" OR tbltickets.name IS NULL, CONCAT(tblclients.firstname, " ", tblclients.lastname), tbltickets.name) as name'),
                DB::raw('IF(tbltickets.email = "" OR tbltickets.email IS NULL, tblclients.email, tbltickets.email) as email'),
                'tbltickets.id'
            )
            ->leftJoin('tblclients', 'tbltickets.userid', '=', 'tblclients.id')
            ->where('tbltickets.status', 'open')
            ->orderBy('tbltickets.date', 'desc')
            ->limit(5)
            ->get();

        // Fetch all admins
        $admins = DB::table('tbladmins')->select('id', 'username', 'firstname', 'lastname')->get();

        // Determine online status based on the latest log entry
        $latestLogs = DB::table('tbladminlog')
            ->select('adminusername', DB::raw('MAX(id) as max_id'))
            ->groupBy('adminusername')
            ->get();

        $onlineAdmins = $latestLogs->filter(function ($log) {
            $latestLogEntry = DB::table('tbladminlog')
                ->where('id', $log->max_id)
                ->first();

            // Check if the login time is within the last 1 days
            $oneDayAgo = Carbon::now()->subDays(1);
            $loginTime = Carbon::parse($latestLogEntry->logintime);

            return $latestLogEntry && $latestLogEntry->logouttime === '0000-00-00 00:00:00' && $loginTime->greaterThanOrEqualTo($oneDayAgo);
        })->pluck('adminusername')->toArray();

        // Prepare admin list with online status
        $adminList = $admins->map(function ($admin) use ($onlineAdmins) {
            $isOnline = in_array($admin->username, $onlineAdmins);
            return [
                'name' => $admin->firstname . ' ' . $admin->lastname,
                'status' => $isOnline ? 'Online' : 'Offline',
            ];
        });

        // Fetch recent activity logs
        $activities = DB::table('tblactivitylog')
            ->join('tbladmins', 'tblactivitylog.user', '=', 'tbladmins.username')
            ->select('tblactivitylog.description', 'tblactivitylog.ipaddr', 'tblactivitylog.date', 'tbladmins.firstname', 'tbladmins.lastname')
            ->orderBy('tblactivitylog.date', 'desc')
            ->limit(5)
            ->get();

        // Count active clients
        $activeClientsCount = DB::table('tblclients')
            ->where('status', 'Active')
            ->count();

        // Count users online (logged in within the last hour)
        $oneHourAgo = \Carbon\Carbon::now()->subHour();
        $usersOnlineCount = DB::table('tblclients')
            ->where('lastlogin', '>=', $oneHourAgo)
            ->count();

        // Fetch all clients for the table
        $clients = DB::table('tblclients')
            ->select('id', 'firstname', 'lastname', 'lastlogin', 'ip')
            ->orderBy('lastlogin', 'desc')
            ->get()
            ->map(function ($client) use ($oneHourAgo) {
                $recentlyLoggedIn = \Carbon\Carbon::parse($client->lastlogin)->greaterThanOrEqualTo($oneHourAgo);
                $client->name = $recentlyLoggedIn ? $client->firstname . ' ' . $client->lastname : '';
                $client->ip = $recentlyLoggedIn ? $client->ip : '';
                $client->lastloggin = \Carbon\Carbon::parse($client->lastlogin)->diffForHumans();
                $client->recentlyLoggedIn = $recentlyLoggedIn;
                return $client;
            });

        // Convert arrays to comma-separated strings
        $orderAllStatusDataString = implode(', ', $orderAllStatusData);
        $orderActiveStatusDataString = implode(', ', $orderActiveStatusData);
        $totalPaidInvoicesDataString = implode(', ', $totalPaidInvoicesData);

        $templatevars = [
            'getPendingOrder' => $getPendingOrder,
            'getSupportAwaitingReply' => $getSupportAwaitingReply,
            'getCancellationRequest' => $getCancellationRequest,
            'getSupportAssignedToYou' => $getSupportAssignedToYou, // Pass the assigned tickets count
            'orderAllStatusDataString' => $orderAllStatusDataString,
            'orderActiveStatusDataString' => $orderActiveStatusDataString,
            'totalPaidInvoicesDataString' => $totalPaidInvoicesDataString,
            'months' => $months,
            'days' => $days, // Pass the days for the last 30 days
            'orderAllStatusLastMonth' => json_encode($orderAllStatusLastMonth),
            'orderActiveStatusLastMonth' => json_encode($orderActiveStatusLastMonth),
            'totalPaidInvoicesLastMonth' => json_encode($totalPaidInvoicesLastMonth),
            'orderAllStatusToday' => json_encode($orderAllStatusToday),
            'orderActiveStatusToday' => json_encode($orderActiveStatusToday),
            'totalPaidInvoicesToday' => json_encode($totalPaidInvoicesToday),
            'billingToday' => $billingToday,
            'billingThisMonth' => $billingThisMonth,
            'billingThisYear' => $billingThisYear,
            'billingAllTime' => $billingAllTime,
            'tickets' => $tickets, // Pass the tickets data to the view
            'adminList' => $adminList, // Pass the admin list to the view
            'activities' => $activities, // Pass the activities to the view
            'activeClientsCount' => $activeClientsCount,
            'usersOnlineCount' => $usersOnlineCount,
            'clients' => $clients, // Pass the clients to the view
        ];

        return view('pages.dashboard.index', $templatevars);
    }

    public function MyAccount()
    {
        $pfx = $this->prefix;
        $user = Auth::guard('admin')->user();
        $role = $user->roleid;

        $roleNameRaw = Role::where('id', $role)->get();
        $roleName = "";

        foreach ($roleNameRaw as $key => $value) {
            $roleName .= $value->name;
        }

        $getSuppDept = Ticketdepartment::all()->pluck('name', 'id')->toArray();

        return view('pages.myaccount.index', [
            'user' => $user,
            'roleName' => $roleName,
            'getSuppDept' => $getSuppDept
        ]);
    }

    public function UpdateMyAccount(Request $request)
    {
        $auth = Auth::guard('web')->user();
        $hashPass = new Password();

        $validator = Validator::make($request->all(), [
            'password' => 'nullable|min:6'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with(['error' => 'Password length minimum is 6 characters']);
        }

        // $passwordHash = (new Password())->hash(\App\Helpers\Sanitize::decode($request->password));

        $updateProfile = Admin::findOrfail($request->userId);
        $updateProfile->firstname = $request->firstname ?? '';
        $updateProfile->lastname = $request->lastname ?? '';
        $updateProfile->email = $request->email ?? '';
        $updateProfile->signature = $request->signature ?? '';
        $updateProfile->notes = $request->notes ?? '';
        $updateProfile->template = $request->template ?? '';
        $updateProfile->language = $request->language ?? '';

        if ($request->password !== $request->password2) {
            return redirect()->back()->with(['error' => 'Password not match! please try again.']);
        } else {
            // $password = $hashPass->hash($request->password);
            $passwordhash = $hashPass->hash($request->password);
            $updateProfile->password = $passwordhash;
            $updateProfile->passwordhash = $passwordhash;
        }

        $updateProfile->save();

        return redirect()->route('admin.dashboard')->with(['success' => 'Your data has been updated, you change it anytime.']);
    }

    public function refreshWidget(Request $request)
    {
        $aInt = new \App\Helpers\Admin("Main Homepage");
        // $aInt->title = \AdminLang::trans("global.hometitle");
        // $aInt->sidebar = "home";
        // $aInt->icon = "home";
        // $aInt->requiredFiles(array("clientfunctions", "invoicefunctions", "gatewayfunctions", "ccfunctions", "processinvoices", "reportfunctions"));
        // $aInt->template = "homepage";

        try {
            $widgetInterface = new \App\Module\Widget();
            $widget = $widgetInterface->getWidgetByName($request->input("widget"));
            $refresh = (bool) $request->input("refresh");
            $widgetOutput = $widget->render($refresh);
            $js = "";

            // foreach ($aInt->getChartFunctions() as $func) {
            //     if (strpos($widgetOutput, $func) !== false) {
            //         $js .= $func . "();";
            //     }
            // }

            if (!empty($js)) {
                $js = "<script>" . $js . "</script>";
            }

            return response()->json(["success" => true, "widgetOutput" => $widgetOutput . $js], 200);
        } catch (\Exception $e) {
            return response()->json(["success" => false, "exceptionMsg" => $e->getMessage()], 200);
        }

        return $aInt;
    }

    public function orderWidgets(Request $request)
    {
        $order = $request->input("order");
        $auth = Auth::guard('admin')->user();
        $adminid = $auth ? $auth->id : 0;
        $admin = \App\User\Admin::find($adminid);

        if ($admin && $admin->widgetOrder != $order) {
            $admin->widgetOrder = $order;
            $admin->save();
        }

        return response()->json(["success" => true], 200);
    }

    public function toggleWidgetDisplay(Request $request)
    {
        $widget = $request->input("widget");

        try {
            // $session = new \WHMCS\Session();
            // $session->create(\WHMCS\Config\Setting::getValue("InstanceID"));

            $auth = Auth::guard('admin')->user();
            $adminid = $auth ? $auth->id : 0;
            $adminUser = \App\User\Admin::find($adminid);
            $currentWidgets = $adminUser->hiddenWidgets;

            if (!in_array($widget, $currentWidgets)) {
                $currentWidgets[] = $widget;
            } else {
                $currentWidgets = array_flip($currentWidgets);
                unset($currentWidgets[$widget]);
                $currentWidgets = array_flip($currentWidgets);
            }

            $adminUser->hiddenWidgets = $currentWidgets;
            $adminUser->save();

            return response()->json(["success" => true], 200);
        } catch (\Exception $e) {
            return response()->json(["success" => false, "widget" => $widget], 200);
        }
    }

    public function getPendingOrderCount()
    {
        $getPendingOrder = DB::table('tblorders')->where('status', 'pending')->count();
        return response()->json(['pendingOrderCount' => $getPendingOrder]);
    }

    public function getSupportAwaitingReply()
    {
        $getSupportAwaitingReply = DB::table('tbltickets')->where('status', 'open')->count();
        return response()->json(['supportAwaitingReplyCount' => $getSupportAwaitingReply]);
    }

    public function getCancellationRequest()
    {
        $getCancellationRequest = DB::table('tblcancelrequests')->count();
        return response()->json(['cancellationRequestCount' => $getCancellationRequest]);
    }
}

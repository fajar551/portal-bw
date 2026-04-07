<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminPermissionsGenerate extends Command
{
    protected $permission = array(
        "1" => "Main Homepage",
        "2" => "Sidebar Statistics",
        "3" => "My Account",
        "4" => "List Clients",
        "5" => "List Services",
        "6" => "List Addons",
        "7" => "List Domains",
        "8" => "Add New Client",
        "104" => "View Clients Summary",
        "120" => "Allow Login as Client",
        "9" => "Edit Clients Details",
        "128" => "View Credit Log",
        "129" => "Manage Credits",
        "10" => "Manage Pay Methods",
        "106" => "Decrypt Full Credit Card Number",
        "107" => "Update/Delete Stored Credit Card",
        "123" => "Attempts CC Captures",
        "11" => "View Clients Products/Services",
        "12" => "Edit Clients Products/Services",
        "99" => "Create Upgrade/Downgrade Orders",
        "13" => "Delete Clients Products/Services",
        "14" => "Perform Server Operations",
        "15" => "View Clients Domains",
        "16" => "Edit Clients Domains",
        "17" => "Delete Clients Domains",
        "98" => "Perform Registrar Operations",
        "95" => "Manage Clients Files",
        "18" => "View Clients Notes",
        "19" => "Add/Edit Client Notes",
        "97" => "Delete Client Notes",
        "20" => "Delete Client",
        "21" => "Mass Mail",
        "22" => "View Cancellation Requests",
        "23" => "Manage Affiliates",
        "24" => "View Orders",
        "25" => "Delete Order",
        "26" => "View Order Details",
        "27" => "Add New Order",
        "130" => "Use Any Promotion Code on Order",
        "28" => "List Transactions",
        "94" => "View Income Totals",
        "29" => "Add Transaction",
        "30" => "Edit Transaction",
        "31" => "Delete Transaction",
        "33" => "List Invoices",
        "34" => "Create Invoice",
        "124" => "Generate Due Invoices",
        "35" => "Manage Invoice",
        "36" => "Delete Invoice",
        "92" => "Refund Invoice Payments",
        "89" => "View Billable Items",
        "90" => "Manage Billable Items",
        "37" => "Offline Credit Card Processing",
        "32" => "View Gateway Log",
        "85" => "Manage Quotes",
        "38" => "Support Center Overview",
        "39" => "Manage Announcements",
        "40" => "Manage Knowledgebase",
        "41" => "Manage Downloads",
        "84" => "Manage Network Issues",
        "42" => "List Support Tickets",
        "105" => "View Support Ticket",
        "121" => "Access All Tickets Directly",
        "82" => "View Flagged Tickets",
        "43" => "Open New Ticket",
        "93" => "Delete Ticket",
        "125" => "Create Predefined Replies",
        "44" => "Manage Predefined Replies",
        "126" => "Delete Predefined Replies",
        "45" => "View Reports",
        "146" => "Client Data Export",
        "88" => "Mass Data Export",
        "46" => "Addon Modules",
        "135" => "Update WHMCS",
        "136" => "Modify Update Configuration",
        "131" => "WHMCSConnect",
        "101" => "Email Marketer",
        "47" => "Link Tracking",
        "49" => "Calendar",
        "50" => "To-Do List",
        "51" => "WHOIS Lookups",
        "52" => "Domain Resolver Checker",
        "53" => "View Integration Code",
        "54" => "WHM Import Script",
        "138" => "Automation Status",
        "55" => "Database Status",
        "56" => "System Cleanup Operations",
        "57" => "View PHP Info",
        "58" => "View Activity Log",
        "59" => "View Admin Log",
        "60" => "View Email Message Log",
        "61" => "View Ticket Mail Import Log",
        "62" => "View WHOIS Lookup Log",
        "103" => "View Module Debug Log",
        "137" => "View Module Queue",
        "63" => "Configure General Settings",
        "148" => "Apps and Integrations",
        "143" => "Configure Sign-In Integration",
        "67" => "Configure Automation Settings",
        "141" => "Manage MarketConnect",
        "145" => "View MarketConnect Balance",
        "144" => "Manage Notifications",
        "147" => "Manage Storage Settings",
        "133" => "Configure Application Links",
        "134" => "Configure OpenID Connect",
        "64" => "Configure Administrators",
        "65" => "Configure Admin Roles",
        "127" => "Configure Two-Factor Authentication",
        "142" => "Manage API Credentials",
        "100" => "Configure Addon Modules",
        "91" => "Configure Client Groups",
        "66" => "Configure Servers",
        "86" => "Configure Currencies",
        "68" => "Configure Payment Gateways",
        "69" => "Tax Configuration",
        "70" => "View Email Templates",
        "113" => "Create/Edit Email Templates",
        "114" => "Delete Email Templates",
        "115" => "Manage Email Template Languages",
        "71" => "View Products/Services",
        "119" => "Manage Product Groups",
        "116" => "Create New Products/Services",
        "117" => "Edit Products/Services",
        "118" => "Delete Products/Services",
        "72" => "Configure Product Addons",
        "102" => "Configure Product Bundles",
        "73" => "View Promotions",
        "108" => "Create/Edit Promotions",
        "109" => "Delete Promotions",
        "74" => "Configure Domain Pricing",
        "75" => "Configure Support Departments",
        "140" => "Configure Escalation Rules",
        "96" => "Configure Ticket Statuses",
        "122" => "Configure Order Statuses",
        "76" => "Configure Spam Control",
        "110" => "View Banned IPs",
        "111" => "Add Banned IP",
        "112" => "Unban Banned IP",
        "77" => "Configure Banned Emails",
        "78" => "Configure Domain Registrars",
        "79" => "Configure Fraud Protection",
        "80" => "Configure Custom Client Fields",
        "87" => "Configure Security Questions",
        "83" => "Configure Database Backups",
        "132" => "Health and Updates",
        "139" => "View What's New",
        "81" => "API Access",
    );

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'adminpermissions:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate permissions used by Admin';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $user = \App\Models\Admin::find(1);
        if (!$user) {
            return $this->error("Admin with userid 1 not found");
        }
        // TODO: assign admin to role
        foreach ($this->permission as $key => $value) {
            $permission = Permission::firstOrCreate(['name' => $value, 'guard_name' => 'admin']);
            $user->givePermissionTo($permission);
        }
        $totalGenratedPermissions = count($this->permission);
        return $this->info("{$totalGenratedPermissions} admin permissions successfully generated");
    }
}

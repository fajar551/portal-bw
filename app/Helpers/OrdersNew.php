<?php
namespace App\Helpers;

use DB, Auth;
use LogActivity, Gateway, Application, ResponseAPI, Cfg;
use Invoice as InvoiceHelper;
use App\Helpers\Hooks;
use App\Helpers\Invoice as HelpersInvoice;

// Import Model Class here
use App\Models\Order;
use App\Models\Hosting;
use App\Models\Hostingaddon;
use App\Models\Hostingconfigoption;
use App\Models\Affiliatesaccount;
use App\Models\Domain;
use App\Models\Invoice;
use App\Models\Invoiceitem;
use App\Models\Orderstatus;
use App\Models\Customfield;
use App\Models\Customfieldsvalue;
use App\Models\Upgrade;
use App\Models\Product;
use App\Models\Addon;
use App\Models\Account;
use App\Models\Pricing;
use App\Models\Client;

// Import Package Class here
use App\Events\CancelOrder;
use App\Events\PendingOrder;
use App\Events\InvoiceCancelled;
use App\Models\Affiliate;
use App\Models\AffiliateAccount;
use App\Models\Link;

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class OrdersNew
{

}

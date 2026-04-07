<?php

use App\Http\Controllers\API\Service\ServiceController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Callback\Nicepay;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

define('COVERAGE_CACHE_KEY', 'coverage_data');

Route::get('/coverage', function () {
   try {
      // Check if coverage data exists in cache
      $coverageData = Cache::get(COVERAGE_CACHE_KEY);

      if (!$coverageData) {
         // Retrieve the list of coverage data from the database
         $coverageData = DB::table("tblpackage_coverage")->get();

         // Cache the data for future requests (assuming it doesn't change frequently)
         Cache::put(COVERAGE_CACHE_KEY, $coverageData, now()->addHours(1)); // Cache for 1 hour
      }

      return response()->json($coverageData);
   } catch (\Exception $e) {
      // Handle potential errors such as database connection issues
      return response()->json(['message' => 'Server error'], 500);
   }
});

Route::get('/product-coverage', function (Request $request) {
   try {
      $productIds = $request->input('product_id');

      // Convert comma-separated product IDs string to an array
      $productIdsArray = array_map('intval', explode(',', $productIds));

      // Fetch product coverage data from the cache if available
      $productCoverageData = Cache::remember('product_coverage_' . $productIds, now()->addMinutes(10), function () use ($productIdsArray) {
         return DB::table('tblproducts')
            ->join('tblpricing', 'tblproducts.id', '=', 'tblpricing.relid')
            ->select('tblproducts.id', 'tblproducts.type', 'tblproducts.gid', 'tblproducts.name', 'tblpricing.monthly', 'tblpricing.quarterly', 'tblpricing.semiannually', 'tblpricing.annually')
            ->whereIn('tblproducts.id', $productIdsArray)
            ->groupBy('tblpricing.relid')
            ->get()
            ->toArray();
      });

      if (empty($productCoverageData)) {
         return response()->json(['message' => 'Data not found'], 404);
      }

      return response()->json($productCoverageData);
   } catch (\Exception $e) {
      // Handle potential errors such as database connection issues
      return response()->json(['message' => 'Server error'], 500);
   }
});

Route::get('/', 'IndexController@index');
Route::match(['get', 'post'], '/index.php', 'IndexController@index');

Route::get('/install', 'InstallController@index')
   ->name('page.install');


Auth::routes(['verify' => true]);
Route::get('/home', 'HomeController@index')->name('home');
Route::match(['get', 'post'], '/addfunds', 'HomeController@AddDepositFunds')->name('deposit.add');
Route::post('/generateinvoice', 'HomeController@GenerateInvoice')->name('generate.invoice');
Route::get('/testingmap', function () {
   return view('pages.dashboard.testingmap');
});
Route::get('/domain/check', 'DomainCheckerController@index')->name('domain.check');
Route::get('/checkcoverage', function () {
   return view('pages.dashboard.checkcoverage');
})->name('checkCoverage');

Route::prefix('')->namespace('Client')->group(function () {
   Route::match(['get', 'post'], '/cart', 'CartController@index')->name('cart');
   // Route::get('/cart', 'CartController@index')->name('cart');
   // Route::post('/cart', 'CartController@index')->name('cart.post');

   Route::match(['get', 'post'], '/dl.php', 'DownloadController@index')->name('dl');
   Route::match(['get', 'post'], 'submitticket.php', 'SupportController@Support_OpenTicket')->name('submitticket')->middleware('auth:web');
   Route::get('viewinvoice.php', 'BillingController@Billing_ViewInvoiceWeb_existing');
   Route::match(['get', 'post'], 'creditcard.php', 'CreditCardController@index');
   Route::post('paymentmethods/remote-input', 'PaymentMethodsController@remoteInput')->name('remoteInput');

   // Profile Controller
   Route::get('detailprofile', 'ProfileController@EditAccountDetails')
      ->name('pages.profile.editaccountdetails.index');
   Route::post('updateprofile', 'ProfileController@UpdateAccountDetails')
      ->name('pages.profile.editaccountdetails.update');
   Route::post('updatepw', 'ProfileController@UpdatePassword')
      ->name('pages.profile.editaccountdetails.updatepw');
   Route::get('emailnotes', 'ProfileController@EmailNotes')
      ->name('pages.profile.emailnotes.index');
   Route::match(['get', 'post'], 'securitysettings', 'ProfileController@SecuritySettings')
      ->name('pages.profile.securitysettings.index');
      
      Route::get('account-security-two-factor-enable', 'ProfileController@enableTwoFactor')
      ->name('account-security-two-factor-enable')
      ->middleware('auth:web');
      
  Route::get('account-security-two-factor-disable', 'ProfileController@disableTwoFactor')
      ->name('account-security-two-factor-disable')
      ->middleware('auth:web');
});
// Upload Account Terms
Route::get('uploadterms', 'HomeController@UploadAccountTerms')
   ->name('pages.profile.uploadaccountterms.index');
// Contact/Sub Account
Route::get('contactsub', 'HomeController@ContactSub')
   ->name('pages.profile.contactsub.index');
Route::get('contactsub.json', 'HomeController@ContactSub_dtJson')
   ->name('dt_Contacts');
Route::post('addcontact', 'HomeController@ContactSub_CreateNew')
   ->name('pages.profile.contactsub.create');
Route::get('contactsub/details/{id}', 'HomeController@ContactSub_Details')
   ->name('pages.profile.contactsub.details');
Route::post('contactsub/update/{id}', 'HomeController@ContactSub_Update')
   ->name('pages.profile.contactsub.update');
Route::delete('delete', 'HomeController@ContactSub_Delete')
   ->name('pages.profile.contactsub.delete');
//Update Password
Route::get('updatepassword', 'HomeController@UpdatePassword')
   ->name('pages.profile.changepassword.index');
//Logout
Route::get('logout', 'HomeController@logout')
   ->name('logout');

Route::prefix('services')->namespace('Client')->group(function () {
   //Service Controller
   Route::match(['get', 'post'], 'upgrade', 'ServicesController@Services_Upgrade')
      ->name('pages.services.upgrade')->middleware('auth:web');
   Route::get('myservices', 'ServicesController@Services_myservices')
      ->name('pages.services.myservices.index')->middleware('auth:web');
   Route::get('dt_myServices', 'ServicesController@dt_myServices')
      ->name('dt_myServices');
   Route::match(['get', 'post'], 'servicedetails/{id}', 'ServicesController@Services_DetailServices')
      ->name('pages.services.myservices.servicedetails')->middleware('auth:web');

    // Route::match(['get', 'post'], 'jalanpintascpanel/{id}', 'ServicesController@Services_DetailJalanPintasCpanel')
    // ->name('pages.services.myservices.jalanPintasCpanel')->middleware('auth:web');

    // cPanel Routes Group
    // cPanel Shortcuts Group
    Route::prefix('jalanpintascpanel')->middleware('auth:web')->group(function () {
        // Main cPanel page
        Route::match(['get',
            'post'
        ], '{id}', 'ServicesController@Services_DetailJalanPintasCpanel')
        ->name('pages.services.myservices.jalanPintasCpanel');

        // Direct cPanel shortcuts
        Route::get('{id}/email', 'ServicesController@Services_RedirectToEmailManager')
        ->name('cpanel.email');
        Route::get('{id}/ftp', 'ServicesController@Services_RedirectToFileManager')
        ->name('cpanel.ftp');
        Route::get('{id}/database', 'ServicesController@Services_RedirectToDatabase')
        ->name('cpanel.database');
        Route::get('{id}/subdomain', 'ServicesController@Services_RedirectToSubdomain')
        ->name('cpanel.subdomain');
        Route::get('{id}/backup', 'ServicesController@Services_RedirectToBackup')
        ->name('cpanel.backup');
        Route::get('{id}/phpmyadmin', 'ServicesController@Services_RedirectToPhpMyAdmin')
        ->name('cpanel.phpmyadmin');
        Route::get('{id}/awstats', 'ServicesController@Services_RedirectToAwstats')
        ->name('cpanel.awstats');
    });
    
   Route::get('cancelservice', 'ServicesController@Services_cancelservice')
      ->name('pages.services.cancelservice.index')->middleware('auth:web');
   Route::get('cartservice', 'ServicesController@Services_CartServices')
      ->name('pages.services.cartservices.index');
   Route::post('getproduct', 'ServicesController@Services_ProductList')
      ->name('pages.services.cartservices.getlist');
   Route::get('confproduct/{pid}', 'ServicesController@Services_ProductList_Configure')
      ->name('pages.services.cartservices.configure');
   Route::post('whoisdomainchecker', 'ServicesController@Service_ProductList_DomainChecker')
      ->name('domaincheck.json');
   Route::post('domainstatus', 'ServicesController@Service_ProductList_DomainStatus')
      ->name('domainstatus.json');
   Route::post('confproduct/orderpost/{id}', 'ServicesController@Service_Order_Post')
      ->name('postDataOrder');
   Route::get('confproduct/orderget/{id}', 'ServicesController@Service_OrderSummary')
      ->name('pages.services.order.config');
   Route::post('configProductOption', 'ServicesController@commandFunction')
      ->name('commandAJAX');
   Route::get('viewcart/{id}', 'ServicesController@Services_ViewCart')
      ->name('pages.services.order.viewchart')->middleware('auth:web');
   Route::post('checkout/{id}', 'ServicesController@Services_CheckOut')
      ->name('checkout');
   Route::post('checkoutapi/{id}', 'ServicesController@Checkout_API')
      ->name('checkoutAPI');
   Route::get('/outofstock/{id}', 'ServicesController@Services_OutOfStock')
      ->name('outofstock');
   Route::get('viewaddons', 'ServicesController@Services_ViewAddons')
      ->name('pages.services.viewaddons.index');
   Route::get('/services/services/cpanel/login/{id}', [
       'as' => 'pages.services.myservices.cpanellogin',
       'uses' => 'ServicesController@Services_LoginCpanel'
   ]);
});

Route::prefix('domain')->namespace('Client')->group(function () {
   Route::get('mydomains', 'DomainsController@Domains_MyDomains')
      ->name('pages.domain.mydomains.index')->middleware('auth:web');
   //Auction
   Route::get('lelangdomains', 'DomainsController@Domains_LelangDomain')
      ->name('pages.domain.lelangdomains.index')->middleware('auth:web');
   Route::post('/lelangdomains/action', 'DomainsController@Domains_LelangDomainAction')
      ->name('pages.domain.lelangdomains.action');
   //Sell Domain
   Route::get('selldomains', 'DomainsController@Domains_SellDomain')
      ->name('pages.domain.selldomains.index')->middleware('auth:web');
   Route::post('/selldomains/action', 'DomainsController@Domains_SellDomainAction')
      ->name('pages.domain.selldomains.action');

   Route::get('transferdomain', 'DomainsController@Domains_TransferDomain')
      ->name('pages.domain.transferdomain.index')->middleware('auth:web');
   Route::get('generatecertificate', 'DomainsController@Generate_Domain_Certificate')
      ->name('generate.domain.certificate');
   Route::get('dt_myDomains', 'DomainsController@dt_myDomains')
      ->name('dt_myDomains')->middleware('auth:web');;
   Route::match(['get', 'post'], 'domaindetails/{id}', 'DomainsController@Domains_DetailDomain')
      ->name('pages.domain.mydomains.domaindetails');

   Route::match(['get', 'post'], '/', 'DomainsController@Domains_DetailDomain2')
      ->name('pages.domain.mydomains.domaindetails2');
   Route::post('update/nameservers', 'DomainsController@Domain_Nameservers_Update')
      ->name('pages.domain.update.nameservers');
   Route::post('update/autorenew', 'DomainsController@Domain_AutoRenew_Update')
      ->name('pages.domain.update.autorenew');
   Route::post('getDomainStat.json', 'DomainsController@DomainStatJson')
      ->name('domainstatjson');
   Route::post('setupdomain', 'DomainsController@Domain_SetupTransfer')
      ->name('pages.domain.domain.setup');
   // Host Child Nameservers
   // Last Updated : 06/11/2024 
   // Author : Anggi
   Route::get('mydomains/childnameservers', 'DomainsController@Domain_Childnameservers')
   ->name('pages.domain.mydomains.childnameservers');
   Route::post('mydomains/childnameservers/get', 'DomainsController@Domain_Childnameservers_Get')
   ->name('pages.domain.mydomains.childnameservers.get');
   Route::post('mydomains/childnameservers/create', 'DomainsController@Domain_Childnameservers_Create')
   ->name('pages.domain.mydomains.childnameservers.create');
   Route::post('mydomains/childnameservers/update', 'DomainsController@Domain_Childnameservers_Update')
   ->name('pages.domain.mydomains.childnameservers.update');
   Route::post('mydomains/childnameservers/delete', 'DomainsController@Domain_Childnameservers_Delete')
   ->name('pages.domain.mydomains.childnameservers.delete');
   // Details Domain
   // Last Updated : 11/11/2024
   // Author : Anggi
   Route::get('mydomains/details', 'DomainsController@Domain_Details')
   ->name('pages.domain.mydomains.details');
   Route::get('mydomains/details/document', 'DomainsController@Domain_Document_Upload')
   ->name('pages.domain.mydomains.details.document');
   Route::get('mydomains/details/requirement', 'DomainsController@Domain_Document_Requirement')
   ->name('pages.domain.mydomains.details.requirement');
   Route::post('mydomains/details/upload', 'DomainsController@uploadDocuments')
   ->name('pages.domain.mydomains.details.upload');
   Route::get('mydomains/details/document/update/{userid}', 'DomainsController@updateListDocuments')
   ->name('pages.domain.mydomains.details.update');
   Route::post('mydomains/details/document/delete', 'DomainsController@deleteFile')
   ->name('pages.domain.mydomains.details.delete');
  /*
   * Author: Anggi
   * Last Updated: 21/11/2024
   * Upload Document
   */
  Route::post('mydomains/details/requirement/detail', 'DomainsController@Domain_Document_Requirement_Detail')
  ->name('pages.domain.mydomains.details.requirement.detail');
  Route::post('mydomains/details/document/tldlookup', 'DomainsController@tldLookup')
  ->name('pages.domain.mydomains.details.tldlookup');
  Route::post('mydomains/details/document/setdocument', 'DomainsController@setDocument')
  ->name('pages.domain.mydomains.details.setdocument');
  //   Route for forward domain modules
  Route::post('mydomains/details/forwarddomain/list', 'DomainsController@DnsListRecords')
  ->name('pages.domain.mydomains.details.forwarddomain.list');
  Route::post('mydomains/details/forwarddomain/create-dns', 'DomainsController@createDns')
  ->name('pages.domain.mydomains.details.forwarddomain.create-dns');
  Route::post('mydomains/details/forwarddomain/init', 'DomainsController@initForwarsDomain')
  ->name('pages.domain.mydomains.details.forwarddomain.init');
  Route::get('mydomains/details/forwarddomain/', 'DomainsController@forwardDomain')
  ->name('pages.domain.mydomains.details.forwarddomain');
  Route::get('mydomains/details/forwardemail/', 'DomainsController@forwardEmail')
  ->name('pages.domain.mydomains.details.forwardemail');
  Route::get('mydomains/details/dnsmanager/', 'DomainsController@forward_domain_clientarea')
  ->name('pages.domain.mydomains.details.dnsmanager');
  Route::post('mydomains/details/forwarddomain/addforwarddomain', 'DomainsController@addForwardDomain')
  ->name('pages.domain.mydomains.details.forwarddomain.addForwardDomain');
   Route::post('mydomains/details/forwarddomain/removeforwarddomain', 'DomainsController@removeForwardDomain')
  ->name('pages.domain.mydomains.details.forwarddomain.removeForwardDomain');
  Route::post('mydomains/details/forwarddomain/addrecordwhm', 'DomainsController@addRecordWHM')
  ->name('pages.domain.mydomains.details.forwarddomain.addrecordwhm');
  Route::post('mydomains/details/forwarddomain/deleterecordwhm', 'DomainsController@deleteRecordWHM')
  ->name('pages.domain.mydomains.details.forwarddomain.deleterecordwhm');
  Route::post('mydomains/details/forwarddomain/editrecordwhm', 'DomainsController@editRecordWHM')
  ->name('pages.domain.mydomains.details.forwarddomain.editrecordwhm');
  Route::post('mydomains/details/forwarddomain/resetrecordwhm', 'DomainsController@resetRecordWHM')
  ->name('pages.domain.mydomains.details.forwarddomain.resetrecordwhm');
  Route::post('mydomains/details/forwarddomain/deletednsrecordwhm', 'DomainsController@deletednsRecordWHM')
  ->name('pages.domain.mydomains.details.forwarddomain.deletednsrecordwhm');
  Route::post('mydomains/details/forwarddomain/addforwardemail', 'DomainsController@addForwardEmail')
  ->name('pages.domain.mydomains.details.forwarddomain.addforwardemail');
  Route::post('mydomains/details/forwarddomain/removeforwardemail', 'DomainsController@removeForwardEmail')
  ->name('pages.domain.mydomains.details.forwarddomain.removeforwardemail');
  Route::post('mydomains/details/forwarddomain/setforwardemail', 'DomainsController@setForwardEmail')
  ->name('pages.domain.mydomains.details.forwarddomain.setforwardemail');
  Route::post('mydomains/details/forwarddomain/unsetforwardemail', 'DomainsController@unsetForwardEmail')
  ->name('pages.domain.mydomains.details.forwarddomain.unsetforwardemail');
});

Route::prefix('billinginfo')->namespace('Client')->group(function () {
   Route::get('myinvoices', 'BillingController@Billing_MyInvoices')
      ->name('pages.billing.myinvoices.index')->middleware('auth:web');
   Route::get('dt_myInvoices', 'BillingController@dt_myInvoices')
      ->name('dt_myInvoices');
   Route::get('viewinvoice/pdf/{id}', 'BillingController@Billing_ViewInvoice')
      ->name('pages.services.mydomains.viewinvoice')->middleware('auth:web');
   Route::match(['get', 'post'], 'viewinvoice/web/{id}', 'BillingController@Billing_ViewInvoiceWeb')
      ->name('pages.services.mydomains.viewinvoiceweb')->middleware('auth:web');
   Route::post('viewinvoice/applycredit/{id}', 'BillingController@BillingInvoice_ApplyCredit')
      ->name('pages.services.mydomains.viewinvoiceweb.applycredit')->middleware('auth:web');
   Route::post('updatepayment', 'BillingController@BillingInvoice_UpdatePayment')
      ->name('pages.services.mydomains.viewinvoiceweb.updatepayment');
   Route::get('manualrequest', 'BillingController@Billing_ManualRequest')
      ->name('pages.billing.manualbillingrequest.index')->middleware('auth:web');
   Route::get('taxinvoice', 'BillingController@Billing_TaxRequest')
      ->name('pages.billing.requesttaxinvoice.index')->middleware('auth:web');
   Route::get('refund', 'BillingController@Billing_Refund')
      ->name('pages.billing.refund.index')->middleware('auth:web');
   Route::get('offerforme', 'BillingController@Billing_Offer')
      ->name('pages.billing.offer.index')->middleware('auth:web');
   Route::get('/loadinvimage', function () {
      return Theme::asset('assets/images/WHMCEPS-dark.png');
   })->name('invoiceimage.url');
});

Route::prefix('support')->namespace('Client')->group(function () {
   Route::get('openticket', 'SupportController@Support_OpenTicket')
       ->name('pages.support.openticket.index')->middleware('auth:web');
   Route::match(['get', 'post'], 'openticket', 'SupportController@Support_OpenTicket')
      ->name('pages.support.openticket.index')->middleware('auth:web');
   Route::get('submitticket/{id}', 'SupportController@Support_SubmitTicket')
      ->name('pages.support.openticket.submitticket')->middleware('auth:web');
   Route::post('postticket', 'SupportController@Support_PostTicket')
      ->name('pages.support.openticket.postticket')->middleware('auth:web');
   Route::get('mytickets', 'SupportController@Support_MyTickets')
      ->name('pages.support.mytickets.index')->middleware('auth:web');
   Route::get('dt_myTickets', 'SupportController@dt_myTickets')
      ->name('dt_myTickets');
   Route::get('viewtickets/{id}', 'SupportController@Support_TicketDetails')
       ->name('pages.support.mytickets.ticketdetails')->middleware('auth:web');
   Route::match(['get', 'post'], 'viewtickets', 'SupportController@Support_TicketDetails')
      ->name('pages.support.mytickets.ticketdetails')->middleware('auth:web');
   Route::get('networkstatus', 'SupportController@Support_NetworkStatus')
      ->name('pages.support.networkstatus.index');
});

Route::prefix('affiliate')->middleware('auth:web')->namespace('Client')->group(function () {
   Route::get('/', 'AffiliateController@Affiliate')
      ->name('pages.affiliate.index');
   Route::get('dtAffiliate', 'AffiliateController@dtAffiliate')
      ->name('dtAffiliate.json');
   Route::post('activateaccount', 'AffiliateController@ActivateAffiliateAccount')
      ->name('pages.affiliate.activateaccount');
   Route::post('withdrawrequest', 'AffiliateController@WithdrawRequest')
      ->name('pages.affiliate.withdrawrequest');
});

Route::prefix("modules/gateways/callback")->group(function () {
   Route::match(['get', 'post'], "{controller}/{method}", "CallbackController@register")->name('modules.gateways.callback');
});

Route::prefix('api/nicepay')->namespace('Callback')->group(function () {
	Route::post('getva', 'Nicepay@va')->name('getVa');
});

Route::fallback(function () {
    return response()->view('error.404', [], 404);
});

// API Public (tanpa auth) - riwayat kerja staff
Route::prefix('api/')->group(function () {
    Route::get('staffbw', [\App\Http\Controllers\API\StaffWorkLogController::class, 'getStaffBw']);
});
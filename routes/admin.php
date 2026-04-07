<?php

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Servers\CPanel\Http\Controllers\CPanelController;
use Modules\Addons\SendInvoiceWa\Http\Controllers\SendInvoiceWaController;
use Modules\Registrar\OpenProvider\Http\Controllers\OpenProviderController;


/*
|--------------------------------------------------------------------------
| Admin Routes{!!  !!}
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "admin" middleware group. Now create something great!
|
*/

Route::namespace('Admin')->group(function () {
    // auth
    Route::namespace('Auth')->group(function () {
        // Route::get('login', 'LoginController@showLoginForm')->name('login');
        // Route::get('password/reset', 'ForgotPasswordController@showLinkRequestForm')->name('password.reset');
        // Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('password.email');
        // Route::post('login', 'LoginController@login')->name('login.submit');
        // Route::post('logout', 'LoginController@logout')->name('logout');

        //Login Routes
        Route::get('/login', 'LoginController@showLoginForm')->name('login');
        Route::post('/login', 'LoginController@login');
        Route::post('/logout', 'LoginController@logout')->name('logout');

        Route::get('/run_cron_invoice', function (Request $request) {
            // $uid = $request->query('userid');
            // Artisan::call('automation', ['--do' => 'CreateInvoices']);
            // $output = Artisan::output();
            // echo "Cek menu invoice di client ini apakah tergenerate >>> <a href='/admin/clients/clientinvoices?userid=".$uid."'>Disini</a>";
            // return $output;

            $string = 'Starter Jogja 15 Mbps (04/01/2024 - 03/02/2024)';

            // Extracting existing dates from the string
            preg_match('/\((.*?) - (.*?)\)/', $string, $matches);
            $startDate = Carbon::parse($matches[1]);
            $endDate = Carbon::parse($matches[2]);

            // Set the new end date
            $newEndDate = Carbon::parse('05/02/2024');

            // Formatting dates
            $formattedDates = '(' . $startDate->format('d/m/Y') . ' - ' . $newEndDate->format('d/m/Y') . ')';

            // Replace original dates with formatted dates
            $newString = preg_replace('/\((.*?) - (.*?)\)/', $formattedDates, $string);

            echo $newString;
        });

        //Forgot Password Routes
        Route::get('/password/reset', 'ForgotPasswordController@showLinkRequestForm')->name('password.request');
        Route::post('/password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('password.email');

        //Reset Password Routes
        Route::get('/password/reset/{token}', 'ResetPasswordController@showResetForm')->name('password.reset');
        Route::post('/password/reset', 'ResetPasswordController@reset')->name('password.update');

        //Confim Password
        Route::get('/password/confirm', 'ConfirmPasswordController@showConfirmForm')->name('password.confirm');
        Route::post('/password/confirm', 'ConfirmPasswordController@confirm')->name('password.confirm');
    });

    // submission
    Route::prefix('submission')->name('submission.')->namespace('Submission')->group(function () {
        Route::prefix('payment')->name('payment.')->group(function () {
            Route::get('/', 'PaymentController@index')->name('index');
        });
    });

    // home
    Route::get('/', 'DashboardController@Dashboard')->name('dashboard');
    Route::match(['get', 'post'], '/widget/refresh', 'DashboardController@refreshWidget')->name('admin-widget-refresh');
    Route::match(['get', 'post'], '/widget/order', 'DashboardController@orderWidgets')->name('admin-widget-order');
    Route::match(['get', 'post'], '/widget/display/toggle', 'DashboardController@toggleWidgetDisplay')->name('admin-widget-display-toggle');

    Route::get('/dashboard', 'DashboardController@Dashboard')->name('pages.dashboard.index');
    Route::get('/myaccount', 'DashboardController@MyAccount')->name('pages.myaccount.index')->middleware(['password.confirm.admin']);
    Route::post('/updateaccount', 'DashboardController@UpdateMyAccount')->name('pages.myaccount.update');
    Route::match(['get', 'post'], 'supporttickets.php', 'Support\SupportticketsController@ViewTicketNEW');
    Route::match(['get', 'post'], 'orders.php', 'Order\ViewOrderController@ViewOrderNEW');
    Route::match(['get', 'post'], 'configauto.php', 'Setup\ConfigAutoController@index')->name("configauto")->middleware(['password.confirm.admin']);
    // clients tab
    Route::prefix('clients')->group(function () {
        // View Clients
        Route::prefix('viewclients')->namespace('Client')->group(function () {
            Route::get('/', 'ClientController@index')->name('pages.clients.viewclients.index');
            Route::get('dt-client.json', 'ClientController@dtClient')->name('pages.clients.viewclients.dtClient');
            Route::any('report-statement', 'ClientController@reportStatementIndex')->name('pages.clients.viewclients.reportstatement.index');
            
            // Route::get('/get-late-client', 'SendInvoiceWaController@getLateClient')->name('GetLateClient');
            // Route::get('/resendWA', 'SendInvoiceWaController@resendInvoiceWA')->name('ResendInvoiceWA');
            // Route::get('/resendWABulk', 'SendInvoiceWaController@resendInvoiceWABulk')->name('ResendInvoiceWABulk');
            
            Route::get('/get-late-client', [SendInvoiceWaController::class, 'getLateClient'])
                ->name('GetLateClient');
                Route::get('/resendWA', [SendInvoiceWaController::class, 'resendInvoiceWA'])
                ->name('ResendInvoiceWA');
            Route::get('/resendWABulk', [SendInvoiceWaController::class, 'resendInvoiceWABulk'])
                ->name('ResendInvoiceWABulk');
            Route::get('/sendCustomWA', [SendInvoiceWaController::class, 'sendCustomWA'])
            ->name('SendCustomWA');
            Route::get('/getGroupName/{groupId}', [SendInvoiceWaController::class, 'getGroupName']);
            Route::get('/resendWAAllClients', [SendInvoiceWaController::class, 'resendInvoiceWAAllClients'])
            ->name('ResendInvoiceWAAllClients');

        });

        // Client Summary
        Route::prefix('clientsummary')->namespace('Client')->group(function () {
            Route::get('/', 'ClientSummaryController@index')->name('pages.clients.viewclients.clientsummary.index');
            Route::get('clientemails', 'ClientSummaryController@clientEmailsIndex')->name('pages.clients.viewclients.clientsummary.clientemails.index');
            Route::post('save-notes', 'ClientSummaryController@saveNotes')->name('pages.clients.viewclients.clientsummary.saveNotes');
            Route::post('upload-file', 'ClientSummaryController@uploadFile')->name('pages.clients.viewclients.clientsummary.uploadFile');
            Route::post('delete-file', 'ClientSummaryController@deleteFile')->name('pages.clients.viewclients.clientsummary.deleteFile');
            Route::get('download-file', 'ClientSummaryController@downloadFile')->name('pages.clients.viewclients.clientsummary.downloadFile');
            Route::post('close-client', 'ClientSummaryController@closeClient')->name('pages.clients.viewclients.clientsummary.closeClient');
            Route::post('delete-client', 'ClientSummaryController@deleteClient')->name('pages.clients.viewclients.clientsummary.deleteClient');
            Route::post('affiliate-activate', 'ClientSummaryController@affiliateActivate')->name('pages.clients.viewclients.clientsummary.affiliateActivate');
            Route::post('merge-client', 'ClientSummaryController@mergeClient')->name('pages.clients.viewclients.clientsummary.mergeClient');
            Route::get('search-client', 'ClientSummaryController@searchClient')->name('pages.clients.viewclients.clientsummary.searchClient');
            Route::post('addfunds', 'ClientSummaryController@addfunds')->name('pages.clients.viewclients.clientsummary.addfunds');
            Route::post('generatedue-invoices', 'ClientSummaryController@generateDueInvoices')->name('pages.clients.viewclients.clientsummary.generateDueInvoices');
            Route::post('mass-action', 'ClientSummaryController@massAction')->name('pages.clients.viewclients.clientsummary.massAction');
            Route::get('dt-product-services.json', 'ClientSummaryController@dtClientSummaryProductServices')->name('pages.clients.viewclients.clientsummary.dtClientSummaryProductServices');
            Route::get('dt-addons.json', 'ClientSummaryController@dtClientSummaryAddons')->name('pages.clients.viewclients.clientsummary.dtClientSummaryAddons');
            Route::get('dt-domain.json', 'ClientSummaryController@dtClientSummaryDomain')->name('pages.clients.viewclients.clientsummary.dtClientSummaryDomain');
            Route::get('dt-quotes.json', 'ClientSummaryController@dtClientSummaryQuotes')->name('pages.clients.viewclients.clientsummary.dtClientSummaryQuotes');
            Route::get('dologin', 'ClientSummaryController@loginAsClient')->name('pages.clients.viewclients.clientsummary.loginAsClient');
            Route::post('csajaxtoggle', 'ClientSummaryController@csajaxtoggle')->name('pages.clients.viewclients.clientsummary.csajaxtoggle');
            Route::post('resendVerificationEmail', 'ClientSummaryController@resendVerificationEmail')->name('pages.clients.viewclients.clientsummary.resendVerificationEmail');
        });

        Route::get('/viewclients/reports', 'ClientsController@ViewClients_clientreports')
            ->name('pages.clients.viewclients.clientreports.index');
        Route::get('/viewclients/merge', 'ClientsController@ViewClients_clientmerge')
            ->name('pages.clients.viewclients.clientmerge.index');

        // Client Profile
        Route::prefix('clientprofile')->namespace('Client')->group(function () {
            Route::get('/', 'ClientProfileController@index')->name('pages.clients.viewclients.clientprofile.index');
            Route::post('update', 'ClientProfileController@update')->name('pages.clients.viewclients.clientprofile.update');
        });

        // Client Contact
        Route::prefix('clientcontacts')->namespace('Client')->group(function () {
            Route::get('/', 'ClientContactController@index')->name('pages.clients.viewclients.clientcontacts.index');
            Route::post('create', 'ClientContactController@create')->name('pages.clients.viewclients.clientcontacts.create');
            Route::post('update', 'ClientContactController@update')->name('pages.clients.viewclients.clientcontacts.update');
            Route::delete('delete', 'ClientContactController@delete')->name('pages.clients.clientcontacts.delete');
            Route::get('dt-contact.json', 'ClientContactController@dtClientContact')->name('pages.clients.viewclients.clientcontacts.dtClientContact');
        });

        // Client Credit (Popup Page)
        Route::prefix('clientcredit')->namespace('Client')->group(function () {
            Route::get('/', 'ClientCreditController@index')->name('pages.clients.viewclients.clientcredit.index');
            Route::get('create', 'ClientCreditController@create')->name('pages.clients.viewclients.clientcredit.create');
            Route::post('store', 'ClientCreditController@store')->name('pages.clients.viewclients.clientcredit.store');
            Route::get('edit', 'ClientCreditController@edit')->name('pages.clients.viewclients.clientcredit.edit');
            Route::post('update', 'ClientCreditController@update')->name('pages.clients.viewclients.clientcredit.update');
            Route::delete('delete', 'ClientCreditController@delete')->name('pages.clients.clientcredit.delete');
            Route::get('dt-client-credit.json', 'ClientCreditController@dtClientCredit')->name('pages.clients.viewclients.clientcredit.dtClientCredit');
        });

        // Client Move (Popup Page)
        Route::prefix('clientmove')->namespace('Client')->group(function () {
            Route::get('/', 'ClientMoveController@index')->name('pages.clients.viewclients.clientmove.index');
            Route::post('transfer', 'ClientMoveController@transfer')->name('pages.clients.viewclients.clientmove.transfer');
        });

        // Client Product/Services
        Route::prefix('clientservices')->namespace('Client')->group(function () {
            Route::get('/', 'ClientServiceController@index')->name('pages.clients.viewclients.clientservices.index');
            Route::get('filter-service', 'ClientServiceController@filterService')->name('pages.clients.viewclients.clientservices.filterService');
            Route::post('update', 'ClientServiceController@update')->name('pages.clients.viewclients.clientservices.update');
            Route::get('create-addon', 'ClientServiceController@createAddon')->name('pages.clients.viewclients.clientservices.createAddon');
            Route::get('edit-addon', 'ClientServiceController@editAddon')->name('pages.clients.viewclients.clientservices.editAddon');
            Route::post('store-addons', 'ClientServiceController@storeAddons')->name('pages.clients.viewclients.clientservices.storeAddons');
            Route::post('update-addons', 'ClientServiceController@updateAddons')->name('pages.clients.viewclients.clientservices.updateAddons');
            Route::post('subscriptionInfo', 'ClientServiceController@subscriptionInfo')->name('pages.clients.viewclients.clientservices.subscriptionInfo');
            Route::post('client-upgrade', 'ClientServiceController@clientUpgrade')->name('pages.clients.viewclients.clientservices.clientUpgrade');
            // Route::post('mod-command', 'ClientServiceController@moduleCommand')->name('pages.clients.viewclients.clientservices.modCommand');
            
            // Route::post('modCommand', [CPanelController::class, 'moduleCommand'])
            //     ->name('admin.pages.clients.viewclients.clientservices.modCommand');

            // Route::post('getProducts', [CPanelController::class, 'getProducts'])
            //     ->name('admin.pages.clients.viewclients.clientservices.getProducts');
            

            // Route::post('getproducts', 'CPanelController@getProducts')
            //     ->name('pages.clients.viewclients.clientservices.getProducts');
            
        });

        Route::middleware(['web', 'auth:admin'])->group(function () {
            Route::prefix('admin/pages/clients/viewclients/clientservices')->group(function () {
                Route::post('/cPanel-module', [CPanelController::class, 'moduleCommand'])
                ->name('pages.clients.viewclients.clientservices.modCommand');

                Route::post('/getProducts', [CPanelController::class, 'getProducts'])
                ->name('pages.clients.viewclients.clientservices.getProducts');
            });
        });
        
        // Client Domain
        Route::prefix('clientdomain')->namespace('Client')->group(function () {
            Route::get('/', 'ClientDomainController@index')->name('pages.clients.viewclients.clientdomain.index');
            Route::get('filter-domain', 'ClientDomainController@filterDomain')->name('pages.clients.viewclients.clientdomain.filterDomain');
            Route::get('register', 'ClientDomainController@register')->name('pages.clients.viewclients.clientdomain.register');

            Route::get('detailContactOpenprovider/{id}', [OpenProviderController::class, 'detailContactOpenprovider'])->name('pages.clients.viewclients.clientdomain.detailContactOpenprovider');
            Route::post('registerOpenprovider', [OpenProviderController::class, 'registerOpenprovider'])->name('pages.clients.viewclients.clientdomain.registerOpenprovider');
            Route::post('renewDomain', [OpenProviderController::class, 'renewDomain'])->name('pages.clients.viewclients.clientdomain.renewDomain');
            Route::post('transferDomain', [OpenProviderController::class, 'transferDomain'])->name('pages.clients.viewclients.clientdomain.transferDomain');
            Route::put('modifyNameServer', [OpenProviderController::class, 'modifyNameServer'])->name('pages.clients.viewclients.clientdomain.modifyNameServer');

            Route::put('updateContact/{id}', [OpenProviderController::class, 'updateContact'])->name('pages.clients.viewclients.clientdomain.updateContact');
            
            Route::post('save-register', 'ClientDomainController@saveRegister')->name('pages.clients.viewclients.clientdomain.saveRegister');
            Route::post('savedomain', 'ClientDomainController@savedomain')->name('pages.clients.viewclients.clientdomain.savedomain');
            Route::post('reg-command', 'ClientDomainController@regCommand')->name('pages.clients.viewclients.clientdomain.regCommand');

            // Route::post('lockDomainOpenprovider', [OpenProviderController::class, 'lockDomainOpenprovider'])->name('pages.clients.viewclients.clientdomain.lockDomainOpenprovider');
            Route::post('lockDomainOpenprovider/{sld}/{tld}', [OpenProviderController::class, 'lockDomainOpenprovider'])->name('pages.clients.viewclients.clientdomain.lockDomainOpenprovider');
            // Route::post('unlockDomainOpenprovider', [OpenProviderController::class, 'unlockDomainOpenprovider'])->name('pages.clients.viewclients.clientdomain.unlockDomainOpenprovider');
            Route::post('unlockDomainOpenprovider/{sld}/{tld}', [OpenProviderController::class, 'unlockDomainOpenprovider'])->name('pages.clients.viewclients.clientdomain.unlockDomainOpenprovider');
            Route::post('lockSpecificDomain', [OpenProviderController::class, 'lockSpecificDomain'])->name('pages.clients.viewclients.clientdomain.lockSpecificDomain');
            Route::get('getDomainList', [OpenProviderController::class, 'getDomainList'])->name('pages.clients.viewclients.clientdomain.getDomainList');
            Route::get('getContactList', [OpenProviderController::class, 'getContactList'])->name('pages.clients.viewclients.clientdomain.getContactList');

            
            Route::post('ssl-check', 'ClientDomainController@sslCheckAdminArea')->name('pages.clients.viewclients.clientdomain.sslCheckAdminArea');
            Route::get('clientdomaincontact', 'ClientDomainController@clientdomaincontact')->name('pages.clients.viewclients.clientdomain.clientdomaincontact');
            Route::post('savedomaincontact', 'ClientDomainController@savedomaincontact')->name('pages.clients.viewclients.clientdomain.savedomaincontact');
            Route::post('updatedomaincontact', 'ClientDomainController@updatedomaincontact')->name('pages.clients.viewclients.clientdomain.updatedomaincontact');
            Route::post('updatenameservers', 'ClientDomainController@updatenameservers')->name('pages.clients.viewclients.clientdomain.updatenameservers');
        });

        Route::get('/clientbillableitems', 'ClientsController@ViewClients_clientbillableitems')
            ->name('pages.clients.viewclients.clientbillableitems.index');

        // Client Invoice
        Route::prefix('clientinvoices')->namespace('Client')->group(function () {
            Route::get('/', 'ClientInvoiceController@index')->name('pages.clients.viewclients.clientinvoices.index');
            Route::delete('delete', 'ClientInvoiceController@delete')->name('pages.clients.viewclients.clientinvoices.delete');
            Route::get('create', 'ClientInvoiceController@create')->name('pages.clients.viewclients.clientinvoices.create');
            Route::post('markpaid-invoice', 'ClientInvoiceController@markPaidInvoice')->name('pages.clients.viewclients.clientinvoices.markPaidInvoice');
            Route::post('markunpaid-invoice', 'ClientInvoiceController@markUnpaidInvoice')->name('pages.clients.viewclients.clientinvoices.markUnpaidInvoice');
            Route::post('markcancelled-invoice', 'ClientInvoiceController@markCancelledInvoice')->name('pages.clients.viewclients.clientinvoices.markCancelledInvoice');
            Route::post('paymentreminder-invoice', 'ClientInvoiceController@paymentReminderInvoice')->name('pages.clients.viewclients.clientinvoices.paymentReminderInvoice');
            Route::post('duplicate-invoice', 'ClientInvoiceController@duplicateInvoice')->name('pages.clients.viewclients.clientinvoices.duplicateInvoice');
            Route::post('merge-invoice', 'ClientInvoiceController@mergeInvoice')->name('pages.clients.viewclients.clientinvoices.mergeInvoice');
            Route::post('masspay-invoice', 'ClientInvoiceController@masspayInvoice')->name('pages.clients.viewclients.clientinvoices.masspayInvoice');
            Route::get('dt-invoices.json', 'ClientInvoiceController@dtClientInvoice')->name('pages.clients.viewclients.clientinvoices.dtClientInvoice');
        });

        // Client Quotes
        Route::prefix('clientquotes')->namespace('Client')->group(function () {
            Route::get('/', 'ClientQuoteController@index')->name('pages.clients.viewclients.clientquotes.index');
            Route::delete('delete', 'ClientQuoteController@delete')->name('pages.clients.viewclients.clientquotes.delete');
            Route::get('dt-quotes.json', 'ClientQuoteController@dtClientQuote')->name('pages.clients.viewclients.clientsummary.dtClientQuote');
        });

        // Client Transaction
        Route::prefix('clienttransactions')->namespace('Client')->group(function () {
            Route::get('/', 'ClientTransactionController@index')->name('pages.clients.viewclients.clienttransactions.index');
            Route::get('create', 'ClientTransactionController@create')->name('pages.clients.viewclients.clienttransactions.create');
            Route::post('store', 'ClientTransactionController@store')->name('pages.clients.viewclients.clienttransactions.store');
            Route::get('edit', 'ClientTransactionController@edit')->name('pages.clients.viewclients.clienttransactions.edit');
            Route::post('update', 'ClientTransactionController@update')->name('pages.clients.viewclients.clienttransactions.update');
            Route::delete('delete', 'ClientTransactionController@delete')->name('pages.clients.viewclients.clienttransactions.delete');
            Route::get('dt-client-transaction.json', 'ClientTransactionController@dtClientTransaction')->name('pages.clients.domainregistrations.dtClientTransaction');
        });

        // Client Tickets
        Route::prefix('clienttickets')->namespace('Client')->group(function () {
            Route::get('/', 'ClientTicketController@index')->name('pages.clients.viewclients.clienttickets.index');
            Route::post('clientticket-command', 'ClientTicketController@clientTicketCommand')->name('pages.clients.viewclients.clienttickets.clientTicketCommand');
            Route::get('dt-client-ticket.json', 'ClientTicketController@dtClientTicket')->name('pages.clients.viewclients.clienttickets.dtClientTicket');
        });

        // Client Emails
        Route::prefix('clientemails')->namespace('Client')->group(function () {
            Route::get('/', 'ClientEmailController@index')->name('pages.clients.viewclients.clientemails.index');
            Route::get('display-message', 'ClientEmailController@displayMessage')->name('pages.clients.viewclients.clientemails.displayMessage');
            Route::delete('delete', 'ClientEmailController@delete')->name('pages.clients.viewclients.clientemails.delete');
            Route::get('resend', 'ClientEmailController@resend')->name('pages.clients.viewclients.clientemails.resend');
            Route::post('do-resend', 'ClientEmailController@doResend')->name('pages.clients.viewclients.clientemails.doResend');
            Route::post('send', 'ClientEmailController@sendMessage')->name('pages.clients.viewclients.clientemails.sendMessage');
            Route::get('dt-client-email.json', 'ClientEmailController@dtClientEmail')->name('pages.clients.viewclients.clientemails.dtClientEmail');
        });

        // Client Notes
        Route::prefix('clientnotes')->namespace('Client')->group(function () {
            Route::get('/', 'ClientNoteController@index')->name('pages.clients.viewclients.clientnotes.index');
            Route::post('store', 'ClientNoteController@store')->name('pages.clients.viewclients.clientnotes.store');
            Route::post('update', 'ClientNoteController@update')->name('pages.clients.viewclients.clientnotes.update');
            Route::delete('delete', 'ClientNoteController@delete')->name('pages.clients.viewclients.clientnotes.delete');
            Route::get('dt-client-note.json', 'ClientNoteController@dtClientNote')->name('pages.clients.viewclients.clientnotes.dtClientNote');
        });

        Route::prefix('clientlog')->namespace('Client')->group(function () {
            Route::get('/', 'ClientLogController@index')->name('pages.clients.viewclients.clientlog.index');
            Route::get('dt-client-log.json', 'ClientLogController@dtClientLog')->name('pages.clients.viewclients.clientlog.dtClientLog');
        });

        // Add New Client
        Route::prefix('addnewclient')->namespace('Client')->group(function () {
            Route::get('/', 'AddClientController@index')->name('pages.clients.addnewclient.index');
            Route::post('create', 'AddClientController@create')->name('pages.clients.addnewclient.create');
        });

        // Product and Services
        Route::prefix('productservices')->namespace('Client')->group(function () {
            Route::get('/product/{serviceType}', 'ProductServiceController@index')->name('pages.clients.productservices.index');
            Route::get('product-service.json', 'ProductServiceController@dtProductService')->name('pages.clients.productservices.dtProductService');
            Route::get('detail', 'ProductServiceController@serviceDetail')->name('pages.clients.productservices.detail');
            // Route::get('/', 'ProductServiceController@ProductServices')->name('pages.clients.productservices.index');
            // Route::get('sharedhosting', 'ProductServiceController@ProductServices_sharedhosting')->name('pages.clients.productservices.sharedhosting.index');
            // Route::get('reselleraccount', 'ProductServiceController@ProductServices_reselleraccount')->name('pages.clients.productservices.reselleraccount.index');
            // Route::get('vpsservers', 'ProductServiceController@ProductServices_vpsservers')->name('pages.clients.productservices.vpsservers.index');
            // Route::get('otherservices', 'ProductServiceController@ProductServices_otherservices')->name('pages.clients.productservices.otherservice.index');
        });

        // Service Addons
        Route::prefix('serviceaddons')->namespace('Client')->group(function () {
            Route::get('/', 'ServiceAddonController@index')->name('pages.clients.serviceaddons.index');
            Route::get('detail', 'ServiceAddonController@addonDetail')->name('pages.clients.serviceaddons.detail');
            Route::get('dt-service-addons.json', 'ServiceAddonController@dtServiceAddons')->name('pages.clients.domainregistrations.dtServiceAddons');
        });

        // Domain Registration
        Route::prefix('domainregistrations')->namespace('Client')->group(function () {
            Route::get('/', 'DomainRegistrationController@index')->name('pages.clients.domainregistrations.index');
            Route::get('dt-domain-registration.json', 'DomainRegistrationController@dtDomainRegistration')->name('pages.clients.domainregistrations.dtDomainRegistration');
            Route::get('detail', 'DomainRegistrationController@domainDetail')->name('pages.clients.domainregistrations.domainDetail');
            Route::post('whois', 'DomainRegistrationController@whois')->name('pages.clients.domainregistrations.whois');
        });

        // Cancellation Requests
        Route::prefix('cancellationrequests')->namespace('Client')->group(function () {
            Route::get('/', 'CancellationRequestController@index')->name('pages.clients.cancellationrequests.index');
            Route::delete('delete-cancellation', 'CancellationRequestController@deleteCancellation')->name('pages.clients.cancellationrequests.deleteCancellation');
            Route::get('dt-cancellation-request.json', 'CancellationRequestController@dtCancellationRequest')->name('pages.clients.cancellationrequests.dtCancellationRequest');
        });

        // Manage Affiliates
        Route::prefix('manageaffiliates')->namespace('Client')->group(function () {
            Route::get('/', 'AffiliateController@index')->name('pages.clients.manageaffiliates.index');
            Route::get('edit', 'AffiliateController@edit')->name('pages.clients.manageaffiliates.edit');
            Route::post('update', 'AffiliateController@update')->name('pages.clients.manageaffiliates.update');
            Route::delete('delete', 'AffiliateController@delete')->name('pages.clients.manageaffiliates.delete');
            Route::get('dt-affiliates.json', 'AffiliateController@dtAffiliates')->name('pages.clients.manageaffiliates.dtAffiliates');
            Route::get('manualpay', 'AffiliateController@manualPay')->name('pages.clients.manageaffiliates.manualpay');
            Route::post('actionCommand', 'AffiliateController@actionCommand')->name('pages.clients.manageaffiliates.actionCommand');
            Route::get('dtReferrals.json', 'AffiliateController@dtReferrals')->name('pages.clients.manageaffiliates.dtReferrals');
            Route::get('dtReferredSignups.json', 'AffiliateController@dtReferredSignups')->name('pages.clients.manageaffiliates.dtReferredSignups');
            Route::get('dtPendingCommissions.json', 'AffiliateController@dtPendingCommissions')->name('pages.clients.manageaffiliates.dtPendingCommissions');
            Route::get('dtCommissionsHistory.json', 'AffiliateController@dtCommissionsHistory')->name('pages.clients.manageaffiliates.dtCommissionsHistory');
            Route::get('dtWithdrawalsHistory.json', 'AffiliateController@dtWithdrawalsHistory')->name('pages.clients.manageaffiliates.dtWithdrawalsHistory');
            Route::post('getrefchart', 'AffiliateController@getChartData')->name('pages.clients.manageaffiliates.getrefchart');
            // TODO: Move to client area
            Route::get('aff', 'AffiliateController@aff')->name('pages.clients.manageaffiliates.aff');
        });

        // Massmail Tools
        Route::prefix('massmail')->namespace('Client')->group(function () {
            Route::get('/', 'MassmailController@index')->name('pages.clients.massmail.index');
            Route::post('sendmessage', 'MassmailController@sendmessage')->name('pages.clients.massmail.sendmessage');
            Route::post('loadmessage', 'MassmailController@loadmessage')->name('pages.clients.massmail.loadmessage');
            Route::post('preview', 'MassmailController@preview')->name('pages.clients.massmail.preview');
            Route::post('send', 'MassmailController@send')->name('pages.clients.massmail.send');
        });
    });

    // Orders
    Route::prefix('orders')->group(function () {
        // List All Order
        Route::prefix('list-allorder')->namespace('Order')->group(function () {
            Route::get('/', 'ListAllOrderController@index')->name('pages.orders.listallorders.index');
            Route::post('actionCommand', 'ListAllOrderController@actionCommand')->name('pages.orders.listallorders.actionCommand');
            Route::get('dt-order.json', 'ListAllOrderController@dtOrder')->name('pages.orders.listallorders.dtOrder');
        });

        // Add Order
        Route::prefix('add-order')->namespace('Order')->group(function () {
            Route::get('/', 'AddOrderController@index')->name('pages.orders.addneworder.index');
            Route::post('actionCommand', 'AddOrderController@actionCommand')->name('pages.orders.addneworder.actionCommand');
            Route::get('/getCycle', 'AddOrderController@getCycle')->name('pages.orders.addneworder.getCycle');
        });


        // View Order
        Route::prefix('view-order')->namespace('Order')->group(function () {
            Route::get('/', 'ViewOrderController@index')->name('pages.orders.vieworder.index');
            Route::post('actionCommand', 'ViewOrderController@actionCommand')->name('pages.orders.vieworder.actionCommand');
            Route::post('update-notes', 'ViewOrderController@updateNotes')->name('pages.orders.vieworder.updateNotes');
        });
    });

    //billing tab
    Route::post('/getclientjson', 'Billing\TransactionlistController@getclientjson')->name('getclientjson');
    Route::prefix('billing')->group(function () {
        Route::prefix('transactionlist')->group(function () {
            Route::get('/', 'Billing\TransactionlistController@TransactionList')->name('transactionlist');
            Route::post('/', 'Billing\TransactionlistController@TransactionListData')->name('transactionlistData');
            Route::post('/store', 'Billing\TransactionlistController@TransactionStore')->name('TransactionStore');
            Route::get('/edit/{id}', 'Billing\TransactionlistController@TransactionEdit')->name('transactionlistEdit');
            Route::put('/update', 'Billing\TransactionlistController@transactionlistUpdate')->name('transactionlistUpdate');
            Route::delete('/destroy', 'Billing\TransactionlistController@TransactionDestroy')->name('transactionlistDestroy');
        });
        Route::prefix('invoices')->group(function () {
            Route::get('/', 'Billing\InvoiceController@Invoices')->name('invoicesIndex');
            Route::post('/', 'Billing\InvoiceController@InvoicesData')->name('invoicesData');
            Route::post('/action', 'Billing\InvoiceController@Action')->name('action');
            Route::get('/add', 'Billing\InvoiceController@Invoices_add')->name('pages.billing.invoices.add');
            Route::delete('/destroy', 'Billing\InvoiceController@InvoicesDestroy')->name('pages.billing.InvoicesDestroy');
            Route::post('/deleteitem', 'Billing\InvoiceController@deleteItemOnInvoice')->name('pages.billing.invoiceitem.delete');
            Route::get('/edit/{id}', 'Billing\InvoiceController@InvoicesEdit')->name('pages.billing.invoices.edit');
            Route::put('/edit/{id}', 'Billing\InvoiceController@InvoicesUpdate')->name('pages.billing.invoices.update');
            Route::get('/view/{id}', 'Billing\InvoiceController@InvoicesView')->name('pages.billing.invoices.download');
            Route::put('/view/{id}', 'Billing\InvoiceController@InvoicesUpdate')->name('pages.billing.invoices.download');
            Route::get('/download/{id}', 'Billing\InvoiceController@InvoicesDownload')->name('pages.billing.invoices.download');
            Route::get('deletetrans', 'Billing\InvoiceController@deletetrans')->name('pages.billing.invoices.deletetrans');
        });

        Route::prefix('billableitemlist')->group(function () {
            Route::get('/', 'Billing\BillableItemsController@index')->name('pages.billing.billableitems.index');
            Route::post('/', 'Billing\BillableItemsController@getData')->name('pages.billing.billableitems.index');
        });


        Route::get('/billableitemlist/add', 'BillingController@BillableItems_add')
            ->name('pages.billing.billableitems.add');
        Route::get('/quotes', 'BillingController@Quotes')
            ->name('pages.billing.quotes.index');
        Route::get('/quotes/add', 'BillingController@Quotes_add')
            ->name('pages.billing.quotes.add');
        Route::get('/offlineccprocessing', 'BillingController@OfflineCCProcessing')
            ->name('pages.billing.offlineccprocessing.index');

        Route::prefix('gatewaylog')->group(function () {
            Route::get('/', 'Billing\GatewaylogController@index')->name('pages.billing.gatewaylog.index');
            Route::post('/', 'Billing\GatewaylogController@GetData')->name('pages.billing.gatewaylog.get-data');
        });
    });

    //support tab
    Route::prefix('support')->group(function () {
        Route::post('/getClientselect2', 'SupportController@client');
        Route::post('/getservice', 'SupportController@getservice');


        Route::prefix('supportoverview')->namespace('Support')->group(function () {
            Route::get('/', 'SupportoverviewController@index')->name('supportoverview_index');
            Route::post('/', 'SupportoverviewController@SupportOverviewPost')->name('pages.support.supportoverview.post');
            Route::post('/supportoverview_pie', 'SupportoverviewController@SupportOverviewPie')->name('pages.support.supportoverview.pie');
        });


        Route::prefix('announcements')->namespace('Support')->group(function () {
            Route::get('/', 'AnnouncementsController@index')->name('announcements_index');
            Route::post('/', 'AnnouncementsController@AnnouncementsGet')->name('pages.support.announcements.get');
            Route::get('/add', 'AnnouncementsController@Announcements_add')->name('pages.support.announcements.add');
            Route::post('/add', 'AnnouncementsController@Announcements_post')->name('pages.support.announcements.post');
            Route::get('/edit/{id}', 'AnnouncementsController@Announcements_edit')->name('pages.support.announcements.edit');
            Route::post('/edit/{id}', 'AnnouncementsController@Announcements_update')->name('pages.support.announcements.update');
            Route::get('/destroy/{id}', 'AnnouncementsController@Announcementsdestroy')->name('pages.support.announcements.destroy');
        });

        Route::prefix('downloads')->namespace('Support')->group(function () {
            Route::get('/', 'DownloadsController@index')->name('downloads.index');
            Route::post('/category-store', 'DownloadsController@CategoryStore')->name('downloads.category_store');
            Route::get('/list', 'DownloadsController@Downloads_list')->name('pages.support.downloads.list');
            Route::get('/detail', 'DownloadsController@Downloads_detail')->name('pages.support.downloads.detail');
        });

        Route::prefix('knowledgebase')->namespace('Support')->group(function () {
            Route::get('/', 'KnowledgebaseController@Knowledgebase')->name('pages.support.knowledgebase.index');
            Route::get('/{id}', 'KnowledgebaseController@Knowledgebase')->name('pages.support.knowledgebase.index');
            Route::post('/category-store', 'KnowledgebaseController@categoryKBStore')->name('pages.support.knowledgebase.categoryStore');
            Route::get('/category-edit/{id}', 'KnowledgebaseController@KnowledgebaseEdit')->name('pages.support.knowledgebase.edit');
            Route::put('/category-update/{id}', 'KnowledgebaseController@KnowledgebaseUpdate')->name('pages.support.knowledgebase.update');
            Route::delete('/category-destroy/{id}', 'KnowledgebaseController@KnowledgebaseDestroy')->name('pages.support.knowledgebase.destroy');
            Route::get('/article/{id}', 'KnowledgebaseController@KnowledgebaseArticle')->name('pages.support.knowledgebase.article');
            Route::post('/article-store', 'KnowledgebaseController@articleStore')->name('knowledgebasearticleStore');
            Route::put('/article-update/{id}', 'KnowledgebaseController@articleUpdate')->name('knowledgebasearticleUpdate');
            Route::delete('/article-destroy/{id}', 'KnowledgebaseController@articleDestroy')->name('knowledgebasearticleDestroy');
            Route::get('/article/view/{id}', 'KnowledgebaseController@KnowledgebaseArticleView');
Route::get('/article/edit/{id}', 'KnowledgebaseController@KnowledgebaseArticle');
        });


        Route::prefix('supporttickets')->namespace('Support')->group(function () {
            // Route::get('/', 'SupportticketsController@index')->name('pages.support.supporttickets.index');
            // Modifikasi route untuk menerima parameter status
             // Rute untuk semua tiket dan tiket dengan status tertentu
                Route::get('/{status?}', 'SupportticketsController@index')
                ->name('pages.support.supporttickets.index')
                ->where('status', 'open|answered|onhold|inprogress|customerreply|closed');
                
            // Rute lainnya untuk supporttickets
            Route::post('/', 'SupportticketsController@SupportTicketsGet')
                ->name('pages.support.supporttickets.get');
            Route::put('/update', 'SupportticketsController@SupportTicketUpdate')->name('pages.support.supporttickets.update');
            Route::post('/split', 'SupportticketsController@SupportTicketSplit')->name('pages.support.supporttickets.split');
            //TODO Andiw
            // Route::post('/getservice', 'SupportController@getservice');
            Route::match(['get', 'post'], '/{id}/view', 'SupportticketsController@ViewTicket')->name('pages.support.supporttickets.view');

            Route::post('/jsaction/predefine', 'SupportticketsController@PredefinedReplies')->name('pages.js.predefine');
            Route::post('/clientlog', 'SupportticketsController@clientlog')->name('pages.support.supporttickets.clientlog');
            Route::post('/tiketothe', 'SupportticketsController@tiketother')->name('pages.support.supporttickets.tiketother');
            Route::prefix('replay')->group(function () {
                Route::delete('/destroy', 'SupportticketsController@ReplayDestroy')->name('ReplayDestroy');
                Route::delete('/delnote', 'SupportticketsController@delnoteDestroy')->name('delnote');
                Route::put('/update', 'SupportticketsController@replayUpdate')->name('repupdate');
                Route::delete('/deleteattachments', 'SupportticketsController@deleteattAchments')->name('deleteattAchments');
            });
        });

        Route::prefix('opennewtickets')->namespace('Support')->group(function () {
            Route::get('/', 'SupportticketsController@OpenNewTickets')->name('pages.support.opennewtickets.index');
            Route::post('/store', 'SupportticketsController@OpenNewTicketsStore')->name('pages.support.opennewtickets.store');
            Route::post('/getClientsWithProduct', 'SupportticketsController@getClientsWithProduct')->name('pages.support.opennewtickets.getClientsWithProduct');
        });

        // Route::get('/predefinedreplies', 'SupportController@PredefinedReplies')->name('pages.support.predefinedreplies.index');
        // Route::post('/predefinedreplies/category-store', 'SupportController@PredefinedRepliesCategoryStore')->name('pages.support.predefinedreplies.category.store');
        // Route::get('/predefinedreplies/category-edit/{id}', 'SupportController@PredefinedRepliesCategoryEdit')->name('pages.support.predefinedreplies.index');
        // Route::put('/predefinedreplies/category-update', 'SupportController@PredefinedRepliesCategoryUpdate')->name('pages.support.predefinedreplies.index');
        // Route::delete('/predefinedreplies/category-destroy/{id}', 'SupportController@PredefinedRepliesCategoryDestroy')->name('pages.support.predefinedreplies.index');

        Route::match(['get', 'post'], '/predefinedreplies', 'SupportController@PredefinedRepliesNEW')->name('pages.support.predefinedreplies.index');

        Route::get('/networkissues', 'SupportController@NetworkIssues')->name('pages.support.networkissues.index');
        Route::post('/networkissues', 'SupportController@NetworkIssuesGet')->name('pages.support.networkissues.get');
        Route::get('/networkissues/add', 'SupportController@NetworkIssues_add')->name('pages.support.networkissues.add');
        Route::post('/networkissues/store', 'SupportController@NetworkIssuesStore')->name('pages.support.networkissues.store');
        Route::get('/networkissues/edit/{id}', 'SupportController@NetworkIssuesEdit')->name('pages.support.networkissues.edit');
        Route::put('/networkissues/update/{id}', 'SupportController@NetworkIssuesUpdate')->name('pages.support.networkissues.update');
        Route::get('/networkissues/destroy/{id}', 'SupportController@NetworkIssuesDestroy')->name('pages.support.networkissues.destroy');
    });

    //setup tab
    Route::prefix('setup')->group(function () {
        Route::prefix('staffmanagement')->namespace('Setup')->group(function () {
            Route::get('/administratorusers', 'StaffManagementController@StaffManagement_adminusers')
                ->name('pages.setup.staffmanagement.administratorusers.index');
            Route::get('dtActiveAdmin.json', 'StaffManagementController@StaffManagement_dtActiveAdmin')
                ->name('pages.setup.staffmanagement.administratorusers.dtActiveAdmin');
            Route::get('dtDisabledAdmin.json', 'StaffManagementController@StaffManagement_dtDisabledAdmin')
                ->name('pages.setup.staffmanagement.administratorusers.dtDisabledAdmin');
            Route::get('/administratorusers/add', 'StaffManagementController@StaffManagement_adminusers_form')
                ->name('pages.setup.staffmanagement.administratorusers.addform');
            Route::post('/administratorusers/insert', 'StaffManagementController@StaffManagement_adminusers_insert')
                ->name('pages.setup.staffmanagement.administratorusers.insert');
            Route::get('/administratorusers/edit/{id}', 'StaffManagementController@StaffManagement_adminusers_edit')
                ->name('pages.setup.staffmanagement.administratorusers.editform');
            Route::post('/administratorusers/update/{id}', 'StaffManagementController@StaffManagement_adminusers_update')
                ->name('pages.setup.staffmanagement.administratorusers.update');
            Route::delete('/administratorusers/delete/{id}', 'StaffManagementController@StaffManagement_adminusers_delete')
                ->name('pages.setup.staffmanagement.administratorusers.delete');

            //*=====================WITH SPATIE PERMISSION PACKAGE============================*//
            Route::get('/administratorroles', 'AdminRolesController@adminRole_index')
                ->name('pages.setup.staffmanagement.administratorroles.index');
            Route::get('/administratorroles/add', 'AdminRolesController@addAdminRoleForm')
                ->name('pages.setup.staffmanagement.administratorroles.add');
            Route::post('/administratorroles/create', 'AdminRolesController@createNewAdminRole')
                ->name('pages.setup.staffmanagement.administratorroles.create');
            Route::get('dtAdminRoles.json', 'AdminRolesController@getAdministratorRoleList')
                ->name('pages.setup.staffmanagement.administratorroles.dtAdminRoles');
            Route::get('/administratorroles/edit/{id}', 'AdminRolesController@editAdminRoleForm')
                ->name('pages.setup.staffmanagement.administratorroles.edit');
            Route::post('/administratorroles/update/{id}', 'AdminRolesController@updateAdminRole')
                ->name('pages.setup.staffmanagement.administratorroles.update');
            Route::delete('/administratorroles/delete/{id}', 'AdminRolesController@deleteAdminRole')
                ->name('pages.setup.staffmanagement.administratorroles.delete');
            // Route::get('/administratorroles', 'StaffManagementController@StaffManagement_adminroles')
            //   ->name('pages.setup.staffmanagement.administratorroles.index');
            // Route::get('dtAdminRoles.json', 'StaffManagementController@StaffManagement_dtAdminRoles')
            //   ->name('pages.setup.staffmanagement.administratorroles.dtAdminRoles');
            // Route::get('/administratorroles/add', 'StaffManagementController@StaffManagement_adminroles_add')
            //   ->name('pages.setup.staffmanagement.administratorroles.add');
            // Route::post('/administratorroles/insert', 'StaffManagementController@StaffManagement_adminroles_insert')
            //   ->name('pages.setup.staffmanagement.administratorroles.insert');
            // Route::get('/administratorroles/edit/{id}', 'StaffManagementController@StaffManagement_adminroles_edit')
            //   ->name('pages.setup.staffmanagement.administratorroles.edit');
            // Route::post('/administratorroles/update/{id}', 'StaffManagementController@StaffManagement_adminroles_update')
            //   ->name('pages.setup.staffmanagement.administratorroles.update');
            // Route::delete('/administratorroles/delete/{id}', 'StaffManagementController@StaffManagement_adminroles_delete')
            //   ->name('pages.setup.staffmanagement.administratorroles.delete');
        });

        Route::prefix('payments')->group(function () {
            Route::get('/currencies', 'SetupController@Payments_currencies')
                ->name('pages.setup.payments.currencies.index');
            Route::post('/currencies/create', 'SetupController@Payments_currencies_create')
                ->name('pages.setup.payments.currencies.create');
            Route::get('/currencies/edit/{id}', 'SetupController@Payments_currencies_edit')
                ->name('pages.setup.payments.currencies.edit');
            Route::put('/currencies/update/{id}', 'SetupController@Payments_currencies_update')
                ->name('pages.setup.payments.currencies.update');
            Route::get('/currencies/delete/{id}', 'SetupController@Payments_currencies_delete')
                ->name('pages.setup.payments.currencies.delete');
            Route::get('/paymentgateways', 'SetupController@Payments_paymentgateways')
                ->name('pages.setup.payments.paymentgateways.index');
            Route::prefix('taxconfiguration')->namespace('Setup\Tax')->group(function () {
                Route::get('/', 'TaxController@index')->name('tax_index');
                Route::post('/general/store', 'TaxController@GeneralStore')->name('generalstore');
                Route::delete('/destroy', 'TaxController@Destroy')->name('generalDestroy');
                Route::post('/rule/store', 'TaxController@RuleStore')->name('rulestore');
            });
            Route::prefix('promotions')->group(function () {
                Route::get('/', 'SetupController@Promotions')->name('pages.setup.payments.promotions.index');
                Route::post('/dt-promotions', 'SetupController@dtPromotions')->name('pages.setup.payments.promotions.dtPromotions');

                Route::post('/', 'SetupController@PromotionsData')->name('pages.setup.payments.promotions.hetdta');
                Route::get('/add', 'SetupController@Promotions_create')->name('pages.setup.payments.promotions.add');
                Route::post('/store', 'SetupController@PromotionsStore')->name('pages.setup.payments.promotions.store');
                Route::get('/edit/{id}', 'SetupController@Promotions_Edit')->name('pages.setup.payments.promotions.edit');
                Route::put('/update', 'SetupController@PromotionsUpdate')->name('pages.setup.payments.promotions.update');
                Route::delete('/destroy', 'SetupController@PromotionsDestroy')->name('pages.setup.payments.promotions.destroy');
                Route::post('/gencode', 'SetupController@Gencode')->name('pages.setup.payments.promotions.Gencode');
                Route::get('/duplicate/{id}', 'SetupController@Duplicate')->name('pages.setup.payments.promotions.Duplicate');
                Route::post('/duplicate-store', 'SetupController@DuplicateStore')->name('pages.setup.payments.promotions.DuplicateStore');
                Route::post('/expired', 'SetupController@expired')->name('pages.setup.payments.promotions.expired');
            });
        });

        // Route::prefix('productservices')->namespace('Setup\ProductConfig')->group(function () {
        //    Route::get('/', 'ProductServiceController@ProductsServices')->name('pages.setup.prodsservices.productservices.index');
        //    Route::get('dt-products.json', 'ProductServiceController@ProductsServices_dtProducts')
        //       ->name('pages.setup.prodsservices.productservices.dtProducts');
        //    Route::get('/creategroup', 'ProductServiceController@ProductsServices_creategroup')
        //       ->name('pages.setup.prodsservices.productservices.creategroup');
        //    Route::post('/creategroup/add', 'ProductServiceController@ProductsServices_creategroup_add')
        //       ->name('pages.setup.prodsservices.productservices.creategroup.add');
        //    Route::get('/createproduct', 'ProductServiceController@ProductsServices_createproduct')
        //       ->name('pages.setup.prodsservices.productservices.createproduct');
        //    Route::post('/productadd', 'ProductServiceController@ProductsServices_createproduct_add')
        //       ->name('pages.setup.prodsservices.productservices.createproduct.add');
        //    Route::get('/product/edit', 'ProductServiceController@ProductsServices_createproduct_edit')
        //       ->name('pages.setup.prodsservices.productservices.createproduct.edit');
        //    Route::post('/product/save/{id}', 'ProductServiceController@ProductsServices_createproduct_update')
        //       ->name('pages.setup.prodsservices.productservices.createproduct.update');
        //    Route::delete('/product/delete/{id}', 'ProductServiceController@ProductServices_createproduct_delete')
        //       ->name('pages.setup.prodsservices.productservices.createproduct.delete');
        // });

        Route::prefix('productservices')->group(function () {
            Route::prefix('configurableoptions')->namespace('Setup\Productservices')->group(function () {
                Route::get('/', 'SetupController@ConfigurableOptions')->name('pages.setup.prodsservices.configurableoptions.index');
                Route::post('/', 'SetupController@getConfigurableOptions')->name('configurableoptionsGet');
                Route::get('/add', 'SetupController@ConfigurableOptions_add')->name('pages.setup.prodsservices.configurableoptions.add');
                Route::post('/store', 'SetupController@ConfigurableOptions_store')->name('pages.setup.prodsservices.configurableoptions.store');
                Route::get('/edit/{id}', 'SetupController@ConfigurableOptions_edit')->name('pages.setup.prodsservices.configurableoptions.edit');
                Route::put('/update', 'SetupController@ConfigurableOptions_update')->name('pages.setup.prodsservices.configurableoptions.update');
                Route::delete('/destroy', 'SetupController@ConfigurableOptionsDestroy')->name('pages.setup.prodsservices.configurableoptions.destroy');
                Route::get('/poppup', 'SetupController@poppup')->name('pages.setup.prodsservices.configurableoptions.poppup');
                Route::post('/poppup', 'SetupController@poppupsave')->name('pages.setup.prodsservices.configurableoptions.poppupsave');
                Route::put('/poppup', 'SetupController@poppupupdate')->name('pages.setup.prodsservices.configurableoptions.putupdate');
                Route::post('/manageoptions', 'SetupController@ConfigurableOptionsManageoptions')->name('pages.setup.prodsservices.configurableoptions.manageoptions');
                Route::delete('/manageoptions/destroy', 'SetupController@ConfigurableOptionsManageoptionsDestroy')->name('pages.setup.prodsservices.configurableoptions.manageoptions.destroy');
                Route::get('/duplicategroup', 'SetupController@Duplicategroup')->name('pages.setup.prodsservices.configurableoptions.duplicategroup');
                Route::post('/duplicategroup', 'SetupController@DuplicategroupStore')->name('pages.setup.prodsservices.configurableoptions.duplicategroupstore');
            });

                Route::prefix('openprovider')->group(function () {
                    Route::get('/test', [
                        \Modules\Registrar\OpenProvider\Http\Controllers\OpenProviderController::class, 
                        'output'
                    ])->name('setup.productservices.openprovider.test');
                    Route::post('/register', [
                        \Modules\Registrar\OpenProvider\Http\Controllers\OpenProviderController::class,
                        'register'
                    ])->name('setup.productservices.openprovider.register');
                });


            //Product Addons
            Route::prefix('productaddons')->namespace('Setup\Productaddon')->group(function () {
                Route::get('/', 'ProductaddonController@index')->name('productaddons.index');
                Route::post('/', 'ProductaddonController@ProductaddonsData')->name('productaddons.data');
                Route::get('/add', 'ProductaddonController@ProductAddons_add')->name('productaddons.add');
                Route::post('/store', 'ProductaddonController@ProductAddons_store')->name('productaddons.store');
                Route::get('/edit/{id}', 'ProductaddonController@ProductAddons_edit')->name('productaddons.edit');
                Route::put('/update', 'ProductaddonController@ProductAddons_update')->name('productaddons.update');
                Route::delete('/destroy', 'ProductaddonController@ProductDestroy')->name('productaddons.destroy');
                Route::delete('/custom_flields_destroy', 'ProductaddonController@ProductDestroyCustomField')->name('productaddons.destroyCustomFields');
            });



            //Product Bundles
            Route::get('/productbundles', 'SetupController@ProductBundles')
                ->name('pages.setup.prodsservices.productbundles.index');
            Route::get('/productbundles/add', 'SetupController@ProductBundles_add')
                ->name('pages.setup.prodsservices.productbundles.add');
            //Domain Pricing
            Route::get('/domainpricing', 'SetupController@DomainPricing')
                ->name('pages.setup.prodsservice.domainpricing.index');
            Route::get('/domainregistrars', 'SetupController@DomainRegistrars')
                ->name('pages.setup.prodsservice.domainregistrars.index');
            //Servers
            Route::get('/serverconfig', 'ServersController@ServerConfig')
                ->name('pages.setup.prodsservice.serverconfig.index');
            Route::get('/serverconfig/add', 'ServersController@ServerConfig_add')
                ->name('pages.setup.prodsservice.serverconfig.add');
            Route::get('/serverconfig/edit', 'ServersController@ServerConfig_edit')
                ->name('pages.setup.prodsservice.serverconfig.edit');
            Route::delete('/serverconfig/delete/{id}', 'ServersController@ServerConfig_delete')
                ->name('pages.setup.prodsservice.serverconfig.delete');
            Route::get('/serverconfig/add-group', 'ServersController@ServerConfig_add_group')
                ->name('pages.setup.prodsservice.serverconfig.add-group');
            Route::get('dtServers', 'ServersController@ServerConfig_dtServers')
                ->name('pages.setup.prodsservice.serverconfig.dtServers');
        });


        Route::prefix('generalsettings')->namespace('Setup')->middleware(['password.confirm.admin'])->group(function () {
            Route::match(['get', 'post'], '/', 'GeneralSettingsController@GeneralSettings')
                ->name('pages.setup.generalsettings.general.index');
            Route::put('/update', 'GeneralSettingsController@GeneralSettings_update')
                ->name('pages.setup.generalsettings.general.update');
            //update for whitelistedIP on security Tab
            Route::post('/whitelistIP', 'GeneralSettingsController@GeneralSettings_whitelistIP')
                ->name('pages.setup.generalsettings.general.whitelist');
            Route::post('/whitelistIP/delete', 'GeneralSettingsController@GeneralSettings_whitelistIP_delete')
                ->name('pages.setup.generalsettings.general.whitelist.delete');
            //update for APIAllowedIPs on security Tab
            Route::post('/apiAllowedIP', 'GeneralSettingsController@GeneralSettings_APIAllowedIps')
                ->name('pages.setup.generalsettings.general.APIAllowedIPs');
            Route::post('/apiAllowedIP/delete', 'GeneralSettingsController@GeneralSettings_APIAllowedIps_delete')
                ->name('pages.setup.generalsettings.general.APIAllowedIPs.delete');
            //Method Payment invoice
            Route::post('postpayment', 'GeneralSettingsController@postPaymentToInvoice')
                ->name('checkPayment');
            //InstaInvoice
            Route::post('updatePay.json', 'GeneralSettingsController@InstaInvoice_UpdatePayment')
                ->name('updatePaymentInvoice');
        });

        Route::get('appsintegrations', 'SetupController@AppsIntegrations')
            ->name('pages.setup.appsintegrations.index');
        Route::get('/signinintegrations', 'SetupController@SignInIntegrations')
            ->name('pages.setup.signinintegrations.index');
        Route::get('/automationsettings', 'SetupController@AutomationSettings')
            ->name('pages.setup.automationsettings.index');
        Route::get('/marketconnect', 'SetupController@MarketConnect')
            ->name('pages.setup.marketconnect.index');
        Route::get('/notifications', 'SetupController@Notifications')
            ->name('pages.setup.notification.index');
        Route::get('/storagesettings', 'SetupController@StorageSettings')
            ->name('pages.setup.storagesettings.index');

        Route::prefix('staffmanagement')->middleware(['password.confirm.admin'])
            ->group(function () {
                Route::get('/2fa', 'SetupController@StaffManagement_2fa')
                    ->name('pages.setup.staffmanagement.2fa.index');
                Route::get('/api', 'SetupController@StaffManagement_apicredentials')
                    ->name('pages.setup.staffmanagement.manageapicredentials.index');
                Route::get('/api/devices', 'DeviceConfigurationController@getDevices')
                    ->name('admin-setup-authz-api-devices-list');
                Route::match(['get', 'post'], '/api/devices/new', 'DeviceConfigurationController@createNew')
                    ->name('admin-setup-authz-api-device-new');
                Route::match(['get', 'post'], '/api/devices/manage/{id?}', 'DeviceConfigurationController@manage')
                    ->name('admin-setup-authz-api-devices-manage');
                Route::post('/api/devices/update/{id?}', 'DeviceConfigurationController@update')
                    ->name('admin-setup-authz-api-devices-update');
                Route::post('/api/devices/delete/{id?}', 'DeviceConfigurationController@delete')
                    ->name('admin-setup-authz-api-devices-delete');
                Route::post('/api/devices/generate', 'DeviceConfigurationController@generate')
                    ->name('admin-setup-authz-api-devices-generate');
                Route::get('/api/roles', 'RoleController@listRoles')
                    ->name('admin-setup-authz-api-roles-list');
                Route::post('/api/roles/delete/{roleId?}', 'RoleController@delete')
                    ->name('admin-setup-authz-api-roles-delete');
                Route::match(['get', 'post'], '/api/roles/manage/{roleId?}', 'RoleController@manage')
                    ->name('admin-setup-authz-api-roles-manage');
                Route::post('/api/roles/create', 'RoleController@create')
                    ->name('admin-setup-authz-api-roles-create');
                // role
                Route::post('/api/get/role', 'SetupController@StaffManagement_apicredentials_get_role')
                    ->name('pages.setup.staffmanagement.manageapicredentials.get.role');
                Route::post('/api/create/role', 'SetupController@StaffManagement_apicredentials_create_role')
                    ->name('pages.setup.staffmanagement.manageapicredentials.create.role');
                Route::post('/api/delete/role', 'SetupController@StaffManagement_apicredentials_delete_role')
                    ->name('pages.setup.staffmanagement.manageapicredentials.delete.role');
                Route::post('/api/edit/role', 'SetupController@StaffManagement_apicredentials_edit_role')
                    ->name('pages.setup.staffmanagement.manageapicredentials.edit.role');
                // credentials
                Route::post('/api/generate/credential', 'SetupController@StaffManagement_apicredentials_generate')
                    ->name('pages.setup.staffmanagement.manageapicredentials.generate');
                Route::post('/api/remove/credential', 'SetupController@StaffManagement_apicredentials_remove')
                    ->name('pages.setup.staffmanagement.manageapicredentials.remove');
                Route::post('/api/get/credential', 'SetupController@StaffManagement_apicredentials_get')
                    ->name('pages.setup.staffmanagement.manageapicredentials.get');
            });

        Route::prefix('staffmanagement')->namespace('Setup')->middleware(['password.confirm.admin'])->group(function () {
            Route::get('/administratorusers', 'StaffManagementController@StaffManagement_adminusers')
                ->name('pages.setup.staffmanagement.administratorusers.index');
            Route::get('dtActiveAdmin.json', 'StaffManagementController@StaffManagement_dtActiveAdmin')
                ->name('pages.setup.staffmanagement.administratorusers.dtActiveAdmin');
            Route::get('dtDisabledAdmin.json', 'StaffManagementController@StaffManagement_dtDisabledAdmin')
                ->name('pages.setup.staffmanagement.administratorusers.dtDisabledAdmin');
            Route::get('/administratorusers/add', 'StaffManagementController@StaffManagement_adminusers_form')
                ->name('pages.setup.staffmanagement.administratorusers.addform');
            Route::post('/administratorusers/insert', 'StaffManagementController@StaffManagement_adminusers_insert')
                ->name('pages.setup.staffmanagement.administratorusers.insert');
            Route::get('/administratorusers/edit/{id}', 'StaffManagementController@StaffManagement_adminusers_edit')
                ->name('pages.setup.staffmanagement.administratorusers.editform');
            Route::post('/administratorusers/update/{id}', 'StaffManagementController@StaffManagement_adminusers_update')
                ->name('pages.setup.staffmanagement.administratorusers.update');
            Route::delete('/administratorusers/delete/{id}', 'StaffManagementController@StaffManagement_adminusers_delete')
                ->name('pages.setup.staffmanagement.administratorusers.delete');

            //*=====================WITH SPATIE PERMISSION PACKAGE============================*//
            Route::get('/administratorroles', 'AdminRolesController@adminRole_index')
                ->name('pages.setup.staffmanagement.administratorroles.index');
            Route::get('/administratorroles/add', 'AdminRolesController@addAdminRoleForm')
                ->name('pages.setup.staffmanagement.administratorroles.add');
            Route::post('/administratorroles/create', 'AdminRolesController@createNewAdminRole')
                ->name('pages.setup.staffmanagement.administratorroles.create');
            Route::get('dtAdminRoles.json', 'AdminRolesController@getAdministratorRoleList')
                ->name('pages.setup.staffmanagement.administratorroles.dtAdminRoles');
            Route::get('/administratorroles/edit/{id}', 'AdminRolesController@editAdminRoleForm')
                ->name('pages.setup.staffmanagement.administratorroles.edit');
            Route::post('/administratorroles/update/{id}', 'AdminRolesController@updateAdminRole')
                ->name('pages.setup.staffmanagement.administratorroles.update');
            Route::delete('/administratorroles/delete/{id}', 'AdminRolesController@deleteAdminRole')
                ->name('pages.setup.staffmanagement.administratorroles.delete');
            // Route::get('/administratorroles', 'StaffManagementController@StaffManagement_adminroles')
            //   ->name('pages.setup.staffmanagement.administratorroles.index');
            // Route::get('dtAdminRoles.json', 'StaffManagementController@StaffManagement_dtAdminRoles')
            //   ->name('pages.setup.staffmanagement.administratorroles.dtAdminRoles');
            // Route::get('/administratorroles/add', 'StaffManagementController@StaffManagement_adminroles_add')
            //   ->name('pages.setup.staffmanagement.administratorroles.add');
            // Route::post('/administratorroles/insert', 'StaffManagementController@StaffManagement_adminroles_insert')
            //   ->name('pages.setup.staffmanagement.administratorroles.insert');
            // Route::get('/administratorroles/edit/{id}', 'StaffManagementController@StaffManagement_adminroles_edit')
            //   ->name('pages.setup.staffmanagement.administratorroles.edit');
            // Route::post('/administratorroles/update/{id}', 'StaffManagementController@StaffManagement_adminroles_update')
            //   ->name('pages.setup.staffmanagement.administratorroles.update');
            // Route::delete('/administratorroles/delete/{id}', 'StaffManagementController@StaffManagement_adminroles_delete')
            //   ->name('pages.setup.staffmanagement.administratorroles.delete');
        });

        Route::prefix('payments')
            ->group(function () {
                Route::get('/currencies', 'SetupController@Payments_currencies')
                    ->name('pages.setup.payments.currencies.index');
                Route::post('/currencies/create', 'SetupController@Payments_currencies_create')
                    ->name('pages.setup.payments.currencies.create');
                Route::get('/currencies/edit/{id}', 'SetupController@Payments_currencies_edit')
                    ->name('pages.setup.payments.currencies.edit');
                Route::put('/currencies/update/{id}', 'SetupController@Payments_currencies_update')
                    ->name('pages.setup.payments.currencies.update');
                Route::get('/currencies/delete/{id}', 'SetupController@Payments_currencies_delete')
                    ->name('pages.setup.payments.currencies.delete');
                Route::get('/paymentgateways', 'SetupController@Payments_paymentgateways')
                    ->name('pages.setup.payments.paymentgateways.index')->middleware(['password.confirm.admin']);
                Route::post('/paymentgateways/activate', 'SetupController@Payments_paymentgateways_activate')
                    ->name('pages.setup.payments.paymentgateways.activate')->middleware(['password.confirm.admin']);
                Route::post('/paymentgateways/deactivate', 'SetupController@Payments_paymentgateways_deactivate')
                    ->name('pages.setup.payments.paymentgateways.deactivate')->middleware(['password.confirm.admin']);
                Route::get('/paymentgateways/moveup', 'SetupController@Payments_paymentgateways_moveup')
                    ->name('pages.setup.payments.paymentgateways.moveup')->middleware(['password.confirm.admin']);
                Route::get('/paymentgateways/movedown', 'SetupController@Payments_paymentgateways_movedown')
                    ->name('pages.setup.payments.paymentgateways.movedown')->middleware(['password.confirm.admin']);
                Route::post('/paymentgateways/save', 'SetupController@Payments_paymentgateways_save')
                    ->name('pages.setup.payments.paymentgateways.save')->middleware(['password.confirm.admin']);
                /*  Route::get('/taxconfiguration', 'SetupController@Payments_taxconfiguration')
                        ->name('pages.setup.payments.taxconfiguration.index');
                    Route::get('/promotions', 'SetupController@Promotions')
                        ->name('pages.setup.payments.promotions.index');
                    Route::get('/promotions/add', 'SetupController@Promotions_create')
                        ->name('pages.setup.payments.promotions.add'); */
            });

        Route::prefix('productservices')->namespace('Setup\ProductConfig')->group(function () {
            //Product Service
            Route::get('/', 'ProductServiceController@ProductsServices')
                ->name('pages.setup.prodsservices.productservices.index');
            Route::get('dt-products.json', 'ProductServiceController@ProductsServices_dtProducts')
                ->name('pages.setup.prodsservices.productservices.dtProducts');
            Route::get('/creategroup', 'ProductServiceController@ProductsServices_creategroup')
                ->name('pages.setup.prodsservices.productservices.creategroup');
            Route::post('/creategroup/add', 'ProductServiceController@ProductsServices_creategroup_add')
                ->name('pages.setup.prodsservices.productservices.creategroup.add');
            Route::get('/editgroup/{id}', 'ProductServiceController@ProductServices_editgroup')
                ->name('pages.setup.prodsservices.productservices.editgroup');
            Route::post('/updategroup/{id}', 'ProductServiceController@ProductServices_updategroup')
                ->name('pages.setup.prodsservices.productservices.updategroup');
            Route::post('/deletegroups/{id}', 'ProductServiceController@ProductService_deletegroup')
                ->name('pages.setup.prodsservices.productservices.deletegroup');
            Route::get('/createproduct', 'ProductServiceController@ProductsServices_createproduct')
                ->name('pages.setup.prodsservices.productservices.createproduct');
            Route::post('/productadd', 'ProductServiceController@ProductsServices_createproduct_add')
                ->name('pages.setup.prodsservices.productservices.createproduct.add');
            Route::get('/duplicateproduct', 'ProductServiceController@ProductsService_createproduct_duplicate')
                ->name('pages.setup.prodsservices.productservices.createproduct.duplicate');
            Route::post('/duplicateproduct/post', 'ProductServiceController@ProductsService_createproduct_duplicate_post')
                ->name('pages.setup.prodsservices.productservices.createproduct.duplicate_post');
            Route::match(['get', 'post'], '/product/edit/{id}', 'ProductServiceController@ProductsServices_createproduct_edit')
                ->name('pages.setup.prodsservices.productservices.createproduct.edit.prods');
            Route::post('/productedit/customfields', 'ProductServiceController@ProductsServices_createproduct_edit_customfields')
                ->name('pages.setup.prodsservices.productservices.createproduct.edit.customfields');
            Route::post('/product/editmodulesettings', 'ProductServiceController@ProductsServices_createproduct_edit_modulesettings')
                ->name('pages.setup.prodsservices.productservices.createproduct.edit.modulesettings');
            Route::post('/product/save/{id}', 'ProductServiceController@ProductsServices_createproduct_update')
                ->name('pages.setup.prodsservices.productservices.createproduct.update');
            Route::post('/product/edit/pricing/{id}', 'ProductServiceController@ProductsServices_createproduct_edit_pricing')
                ->name('pages.setup.prodsservices.productservices.createproduct.edit_pricing');
            Route::post('/product/edit/details/{id}', 'ProductServiceController@ProductsServices_createproduct_edit_details')
                ->name('pages.setup.prodsservices.productservices.createproduct.edit_details');
            Route::post('/product/edit/configoptions/{id}', 'ProductServiceController@ProductsServices_createproduct_edit_configurableoptions')
                ->name('pages.setup.prodsservices.productservices.createproduct.edit_configoptions');
            Route::post('/product/edit/upgrades/{id}', 'ProductServiceController@ProductsServices_createproduct_edit_upgrades')
                ->name('pages.setup.prodsservices.productservices.createproduct.edit_upgrades');
            Route::post('/product/edit/freedomains/{id}', 'ProductServiceController@ProductsServices_createproduct_edit_freedomains')
                ->name('pages.setup.prodsservices.productservices.createproduct.edit_freedomains');
            Route::post('/test-json', 'ProductServiceController@postPricingAjax')
                ->name('pages.setup.prodsservices.productservices.createproduct.ajax');
            Route::delete('/product/delete/{id}', 'ProductServiceController@ProductServices_createproduct_delete')
                ->name('pages.setup.prodsservices.productservices.createproduct.delete.prods');

            //Domain
            Route::match(['get', 'post'], '/domainpricing', 'DomainController@DomainPricing')
                ->name('pages.setup.prodsservice.domainpricing.index');
            Route::get('/domainregistrars', 'DomainController@DomainRegistrars')
                ->name('pages.setup.prodsservice.domainregistrars.index');
            Route::post('/domainregistrars/active', 'DomainController@DomainRegistrars_active')
                ->name('pages.setup.prodsservice.domainregistrars.active');
            Route::post('/domainregistrars/deactive', 'DomainController@DomainRegistrars_deactive')
                ->name('pages.setup.prodsservice.domainregistrars.deactive');

            //Servers
            Route::get('/serverconfig', 'ServersController@ServerConfig')
                ->name('pages.setup.prodsservice.serverconfig.index');
            Route::get('/serverconfig/add', 'ServersController@ServerConfig_add')
                ->name('pages.setup.prodsservice.serverconfig.add');
            Route::post('/serverconfig/insert', 'ServersController@ServerConfig_insert')
                ->name('pages.setup.prodsservice.serverconfig.insert');
            Route::get('/serverconfig/edit/{id}', 'ServersController@ServerConfig_edit')
                ->name('pages.setup.prodsservice.serverconfig.edit');
            Route::post('/serverconfig/update/{id}', 'ServersController@ServerConfig_update')
                ->name('pages.setup.prodsservice.serverconfig.update');
            Route::post('/serverconfig/activate/{id}', 'ServersController@ServerConfig_disabledServer')
                ->name('pages.setup.prodsservice.serverconfig.updateServer');
            Route::delete('/serverconfig/delete/{id}', 'ServersController@ServerConfig_delete')
                ->name('pages.setup.prodsservice.serverconfig.delete');
            Route::get('/serverconfig/add-group', 'ServersController@ServerConfig_add_group')
                ->name('pages.setup.prodsservice.serverconfig.add-group');
            Route::get('dtServers', 'ServersController@ServerConfig_dtServers')
                ->name('pages.setup.prodsservice.serverconfig.dtServers');
            Route::post('/serverconfig/add-group/insert', 'ServersController@ServersConfig_add_group_insert')
                ->name('pages.setup.prodsservice.serverconfig.add-group-insert');
            Route::get('dtServerGroup', 'ServersController@ServerConfig_dtServerGroup')
                ->name('pages.setup.prodsservice.serverconfig.dtServerGroup');
            Route::get('/serverconfig/edit-group/{id}', 'ServersController@ServerConfig_edit_group')
                ->name('pages.setup.prodsservice.serverconfig.edit-group');
            Route::post('/serverconfig/update-group/{id}', 'ServersController@ServerConfig_group_update')
                ->name('pages.setup.prodsservice.serverconfig.update-group');
            Route::delete('/serverconfig/delete-group/{id}', 'ServersController@ServerConfig_group_delete')
                ->name('pages.setup.prodsservice.serverconfig.delete-group');
        });

        Route::prefix('productservices')->namespace('Setup\Productservices')->group(function () {
            Route::prefix('configurableoptions')->group(function () {
                Route::get('/', 'SetupController@ConfigurableOptions')->name('pages.setup.prodsservices.configurableoptions.index');
                Route::post('/', 'SetupController@getConfigurableOptions')->name('configurableoptionsGet');
                Route::get('/add', 'SetupController@ConfigurableOptions_add')->name('pages.setup.prodsservices.configurableoptions.add');
                Route::post('/store', 'SetupController@ConfigurableOptions_store')->name('pages.setup.prodsservices.configurableoptions.store');
                Route::get('/edit/{id}', 'SetupController@ConfigurableOptions_edit')->name('pages.setup.prodsservices.configurableoptions.edit');
                Route::put('/update', 'SetupController@ConfigurableOptions_update')->name('pages.setup.prodsservices.configurableoptions.update');
                Route::delete('/destroy', 'SetupController@ConfigurableOptionsDestroy')->name('pages.setup.prodsservices.configurableoptions.destroy');
                Route::post('/manageoptions', 'SetupController@ConfigurableOptionsManageoptions')->name('pages.setup.prodsservices.configurableoptions.manageoptions');
            });
            //Product Addons
            // Route::get('/productaddons', 'SetupController@ProductAddons')
            //     ->name('pages.setup.prodsservices.productaddons.index');
            // Route::get('/productaddons/add', 'SetupController@ProductAddons_add')
            //     ->name('pages.setup.prodsservices.productaddons.index');
            //Product Bundles
            // Route::get('/productbundles', 'SetupController@ProductBundles')
            //     ->name('pages.setup.prodsservices.productbundles.index');
            // Route::get('/productbundles/add', 'SetupController@ProductBundles_add')
            //     ->name('pages.setup.prodsservices.productbundles.add');
            //Domain Pricing
            // Route::get('/domainpricing', 'DomainController@DomainPricing')
            //   ->name('pages.setup.prodsservice.domainpricing.index');
            // Route::get('/domainregistrars', 'DomainController@DomainRegistrars')
            //   ->name('pages.setup.prodsservice.domainregistrars.index');
        });

        Route::prefix('support')->group(function () {
            Route::prefix('configticketdepartments')->namespace('Setup\Support')->group(function () {
                Route::get('/', 'SupportDepartmensController@index')->name('supportticketdepartments_index');
                Route::post('/', 'SupportDepartmensController@getData')->name('supportticketdepartments_getData');
                Route::get('/add', 'SupportDepartmensController@add')->name('pages.setup.support.supportticketdepartments.add');
                Route::post('/store', 'SupportDepartmensController@store')->name('pages.setup.support.supportticketdepartments.store');
                Route::get('/edit/{id}', 'SupportDepartmensController@Support_ticketdepartments_edit')->name('pages.setup.support.supportticketdepartments.edit');
                Route::put('/update', 'SupportDepartmensController@update')->name('pages.setup.support.supportticketdepartments.update');
                Route::delete('/destroy', 'SupportDepartmensController@destroy')->name('supportticketdepartments_destroy');
                Route::put('/order', 'SupportDepartmensController@order')->name('supportticketdepartments_order');
            });
            Route::prefix('ticketstatuses')->namespace('Setup\Ticket')->group(function () {
                Route::get('/', 'TicketstatusController@index')->name('ticketstatuses.index');
                Route::post('/', 'TicketstatusController@indextable')->name('ticketstatuses.indextable');
                Route::post('/store', 'TicketstatusController@store')->name('store.indextable');
                Route::put('/update', 'TicketstatusController@update')->name('update.indextable');
                Route::delete('/destroy', 'TicketstatusController@destroy')->name('destroy.indextable');
            });
            Route::prefix('escalationrules')->namespace('Setup\Escalationrules')->group(function () {
                Route::get('/', 'EscalationrulesController@index')->name('escalationrules.index');
                Route::post('/', 'EscalationrulesController@indexdata')->name('escalationrules.indexdata');
                Route::get('/add', 'EscalationrulesController@add')->name('escalationrules.add');
                Route::post('/store', 'EscalationrulesController@store')->name('escalationrules.store');
                Route::get('/edit/{id}', 'EscalationrulesController@edit')->name('escalationrules.edit');
                Route::put('/update', 'EscalationrulesController@update')->name('escalationrules.update');
                Route::delete('/destroy', 'EscalationrulesController@destroy')->name('escalationrules.destroy');
            });

            Route::get('/spamcontrol', 'SetupController@Support_spamcontrol')->name('pages.setup.support.spamcontrol.index');
        });

        Route::get('/applicationlinks', 'SetupController@ApplicationLinks')
            ->name('pages.setup.applicationlinks.index');
        Route::get('/openidconnect', 'SetupController@openIdConnect')
            ->name('pages.setup.openidconnect.index');
        Route::get('/openidconnect/add', 'SetupController@openIdConnect_add')
            ->name('pages.setup.openidconnect.add');
        Route::get('/emailtemplates', 'SetupController@EmailTemplates')
            ->name('pages.setup.emailtemplates.index');
        Route::post('/emailtemplates/create', 'SetupController@EmailTemplates_create')
            ->name('pages.setup.emailtemplates.create');
        Route::get('/emailtemplates/{id}/edit', 'SetupController@EmailTemplates_edit')
            ->name('pages.setup.emailtemplates.edit');
        Route::put('/emailtemplates/update/{id}', 'SetupController@EmailTemplates_update')
            ->name('pages.setup.emailtemplates.update');
        Route::get('/emailtemplates/delete/{id}', 'SetupController@EmailTemplates_delete')
            ->name('pages.setup.emailtemplates.delete');
        Route::get('/addonsmodule', 'SetupController@AddonsModule')
            ->name('pages.setup.addonsmodule.index');
        Route::post('/addonsmodule/active', 'SetupController@AddonsModule_active')
            ->name('pages.setup.addonsmodule.active');
        Route::post('/addonsmodule/deactive', 'SetupController@AddonsModule_deactive')
            ->name('pages.setup.addonsmodule.deactive');
        Route::match(['get', 'post'], '/clientgroups', 'SetupController@ClientGroups')
            ->name('pages.setup.clientgroups.index');
        Route::get('/customclientfields', 'SetupController@CustomClientFields')
            ->name('pages.setup.customclientfields.index');
        Route::post('/customclientfields/save', 'SetupController@CustomClientFields_save')
            ->name('pages.setup.customclientfields.save');
        Route::get('/customclientfields/delete/{id}', 'SetupController@CustomClientFields_delete')
            ->name('pages.setup.customclientfields.delete');
        Route::get('/fraudprotection', 'SetupController@FraudProtection')
            ->name('pages.setup.fraudprotection.index');

        Route::prefix('other')
            ->group(function () {
                Route::get('/orderstatuses', 'SetupController@Other_orderstatuses')
                    ->name('pages.setup.other.orderstatuses.index');
                Route::get('/securityquestions', 'SetupController@Other_securityquestions')
                    ->name('pages.setup.other.securityquestions.index');
                Route::post('/securityquestions/post', 'SetupController@Other_securityquestions_post')
                    ->name('pages.setup.other.securityquestions.post');
                Route::post('/securityquestions/delete', 'SetupController@Other_securityquestions_delete')
                    ->name('pages.setup.other.securityquestions.delete');
                Route::get('/bannedips', 'SetupController@Other_bannedips')
                    ->name('pages.setup.other.bannedips.index');
                Route::get('/bannedemails', 'SetupController@Other_bannedemails')
                    ->name('pages.setup.other.bannedemails.index');
                Route::get('/databasebackups', 'SetupController@Other_databasebackups')
                    ->name('pages.setup.other.databasebackups.index');
            });
        Route::prefix('log')->namespace('Setup\Log')->group(function () {
            Route::get('activitylog', 'LogController@activitylog')->name('activitylog.index');
            Route::post('activitylog', 'LogController@getactivitylog')->name('activitylog.ajax');
            Route::get('adminlog', 'LogController@adminlog')->name('adminlog.index');
            Route::post('adminlog', 'LogController@getadminlog')->name('adminlog.ajax');
            Route::get('modulelog', 'LogController@modulelog')->name('modulelog.index');
            Route::post('modulelog', 'LogController@getmodulelog')->name('modulelog.ajax');
            Route::get('emailmessagelog', 'LogController@emailmessagelog')->name('emailmessagelog.index');
            Route::post('emailmessagelog', 'LogController@getemailmessagelog')->name('emailmessagelog.ajax');
            Route::get('ticketmailimportlog', 'LogController@ticketmailimportlog')->name('ticketmailimportlog.index');
            Route::post('ticketmailimportlog', 'LogController@getticketmailimportlog')->name('ticketmailimportlog.ajax');
            Route::get('whoislookuplog', 'LogController@whoislookuplog')->name('whoislookuplog.index');
            Route::post('whoislookuplog', 'LogController@getwhoislookuplog')->name('whoislookuplog.ajax');
        });
    });
    Route::namespace('Search')->group(function () {
        Route::post('intelligent-search', 'SearchController@index')->name('intelligentsearch');
    });

    Route::get('addonsmodule', 'AddonsModuleController@index')->name('addonsmodule')->middleware('auth:admin');
});

Route::fallback(function () {
    return response()->view('error.404', [], 404);
});
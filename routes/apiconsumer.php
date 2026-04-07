<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes used for Internal
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/**
 * @group Admin API
 * 
 * used for admin area without permissions
 */
Route::namespace('Admin\API')->name('admin.')->prefix('admin')->group(function () {
    // Setup
    Route::name('setup.')->prefix('setup')->group(function () {
        Route::post('removeEmailTemplateAttachment', 'SetupController@removeEmailTemplateAttachment')->name('removeEmailTemplateAttachment');
        Route::post('saveAddonsModuleConfig', 'SetupController@saveAddonsModuleConfig')->name('saveAddonsModuleConfig');
        Route::post('saveRegistrarModuleConfig', 'SetupController@saveRegistrarModuleConfig')->name('saveRegistrarModuleConfig');
        Route::post('fetchModuleSettings', 'SetupController@fetchModuleSettings')->name('fetchModuleSettings');
        Route::post('addnewTld', 'DomainController@addnewTld')->name('addnewTld');
        Route::post('deleteTld', 'DomainController@deleteTld')->name('deleteTld');
        Route::post('deleteTld', 'DomainController@deleteTld')->name('deleteTld');
        Route::post('saveorderTld', 'DomainController@saveorder')->name('saveorderTld');
        Route::post('saveTld', 'DomainController@save')->name('saveTld');
        Route::post('togglePremiumDomain', 'DomainController@togglePremium')->name('togglePremiumDomain');
        Route::post('showduplicatetld', 'DomainController@showduplicatetld')->name('showduplicatetld');
        Route::post('duplicatetld', 'DomainController@duplicatetld')->name('duplicatetld');
        Route::post('premium-levels', 'DomainController@premiumlevels')->name('premium-levels');
        Route::post('delete-premium', 'DomainController@deletepremium')->name('delete-premium');
        Route::post('lookup-provider', 'DomainController@lookupprovider')->name('lookup-provider');
        Route::post('saveaddons', 'DomainController@saveaddons')->name('saveaddons');
        Route::post('mass-configuration-tld', 'TldController@massConfiguration')->name('mass-configuration-tld');
        
        // domain lookup
        Route::name('domainlookup.')->prefix('domainlookup')->group(function() {
            Route::post('/', 'DomainLookupController@index')->name('index');
        });
    });

    // Billing
    Route::name('billing.')->prefix('billing')->group(function () {
        Route::post('massActionInvoiceItems', 'BillingController@massActionInvoiceItems')->name('mass-action-invoice-items');
    });
});


/**
 * @group Client API
 * 
 * used for client area
 */
// Route::namespace('Client\API')->name('client.')->prefix('client')->group(function () {
    // code ...
// });

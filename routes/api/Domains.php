<?php

use App\Helpers\ResponseAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the 'api' middleware group. Enjoy building your API!
|
*/

Route::namespace('API\Domains')->group(function () {
	Route::post('DomainWhois', 'DomainsController@DomainWhois')->name('DomainWhois')->middleware('permissionapi:DomainWhois,admin')->middleware('permissionapi:DomainWhois,admin');
	Route::post('GetTLDPricing', 'DomainsController@GetTLDPricing')->name('GetTLDPricing')->middleware('permissionapi:GetTLDPricing,admin');
	Route::post('/UpdateClientDomain', 'DomainsController@UpdateClientDomain')->name('UpdateClientDomain')->middleware('permissionapi:UpdateClientDomain,admin');
	Route::post('/DomainGetLockingStatus', 'DomainsController@DomainGetLockingStatus')->name('DomainGetLockingStatus')->middleware('permissionapi:DomainGetLockingStatus,admin');
	Route::post('/DomainGetNameservers', 'DomainsController@DomainGetNameservers')->name('DomainGetNameservers')->middleware('permissionapi:DomainGetNameservers,admin');
	Route::post('/DomainGetWhoisInfo', 'DomainsController@DomainGetWhoisInfo')->name('DomainGetWhoisInfo')->middleware('permissionapi:DomainGetWhoisInfo,admin');
	Route::post('/DomainRegister', 'DomainsController@DomainRegister')->name('DomainRegister')->middleware('permissionapi:DomainRegister,admin');
	//Route::post('/GetHealthStatus', 'DomainsController@GetHealthStatus');
	//Route::post('/GetServers', 'DomainsController@GetServers');
	Route::post('/DomainRelease', 'DomainsController@DomainRelease')->name('DomainRelease')->middleware('permissionapi:DomainRelease,admin');
	Route::post('/DomainRenew', 'DomainsController@DomainRenew')->name('DomainRenew')->middleware('permissionapi:DomainRenew,admin');
	Route::post('/DomainRequestEPP', 'DomainsController@DomainRequestEPP')->name('DomainRequestEPP')->middleware('permissionapi:DomainRequestEPP,admin');
	Route::post('/DomainToggleIdProtect', 'DomainsController@DomainToggleIdProtect')->name('DomainToggleIdProtect')->middleware('permissionapi:DomainToggleIdProtect,admin');
	Route::post('/DomainTransfer', 'DomainsController@DomainTransfer')->name('DomainTransfer')->middleware('permissionapi:DomainTransfer,admin');
	Route::post('/DomainUpdateLockingStatus', 'DomainsController@DomainUpdateLockingStatus')->name('DomainUpdateLockingStatus')->middleware('permissionapi:DomainUpdateLockingStatus,admin');
	Route::post('/DomainUpdateNameservers', 'DomainsController@DomainUpdateNameservers')->name('DomainUpdateNameservers')->middleware('permissionapi:DomainUpdateNameservers,admin');
	Route::post('/DomainUpdateWhoisInfo', 'DomainsController@DomainUpdateWhoisInfo')->name('DomainUpdateWhoisInfo')->middleware('permissionapi:DomainUpdateWhoisInfo,admin');
});

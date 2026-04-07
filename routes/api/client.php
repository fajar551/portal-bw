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

Route::namespace('API\Client')->group(function () {
	Route::post('AddClient', 'ClientV2Controller@AddClient')->name('AddClient')->middleware('permissionapi:AddClient,admin', 'api');
	Route::post('UpdateClient', 'ClientController@UpdateClient')->name('UpdateClient')->middleware('permissionapi:UpdateClient,admin');
	Route::post('/AddContact', 'ClientController@AddContact')->name('AddContact')->middleware('permissionapi:AddContact,admin');
	Route::post('/CloseClient', 'ClientController@CloseClient')->name('CloseClient')->middleware('permissionapi:CloseClient,admin');
	Route::post('/DeleteClient', 'ClientController@DeleteClient')->name('DeleteClient')->middleware('permissionapi:DeleteClient,admin');
	Route::post('/DeleteContact', 'ClientController@DeleteContact')->name('DeleteContact')->middleware('permissionapi:DeleteContact,admin');
	Route::post('/GetClientPassword', 'ClientV2Controller@GetClientPassword')->name('GetClientPassword')->middleware('permissionapi:GetClientPassword,admin');
	Route::post('/GetClients', 'ClientV2Controller@GetClients')->name('GetClients')->middleware('permissionapi:GetClients,admin');
	Route::post('/GetClientGroups', 'ClientV2Controller@GetClientGroups')->name('GetClientGroups')->middleware('permissionapi:GetClientGroups,admin');
	Route::post('GetClientsDetails', 'ClientV2Controller@GetClientsDetails')->name('GetClientsDetails')->middleware('permissionapi:GetClientsDetails,admin');
	Route::post('/GetClientsAddons', 'ClientV2Controller@GetClientsAddons')->name('GetClientsAddons')->middleware('permissionapi:GetClientsAddons,admin');
	Route::post('GetClientsDomains', 'ClientV2Controller@GetClientsDomains')->name('GetClientsDomains')->middleware('permissionapi:GetClientsDomains,admin')->middleware('permissionapi:GetClientsDomains,admin');
	Route::post('/GetContacts', 'ClientV2Controller@GetContacts')->name('GetContacts')->middleware('permissionapi:GetContacts,admin');
	Route::post('/GetClientsProducts', 'ClientV2Controller@GetClientsProducts')->name('GetClientsProducts')->middleware('permissionapi:GetClientsProducts,admin');
	Route::post('/GetEmails', 'ClientV2Controller@GetEmails')->name('GetEmails')->middleware('permissionapi:GetEmails,admin');
	Route::post('/UpdateContact', 'ClientController@UpdateContact')->name('UpdateContact')->middleware('permissionapi:UpdateContact,admin');
	Route::post('/GetCancelledPackages', 'ClientV2Controller@GetCancelledPackages')->name('GetCancelledPackages')->middleware('permissionapi:GetCancelledPackages,admin');

});

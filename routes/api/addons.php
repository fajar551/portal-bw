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

Route::namespace('API\Addons')->group(function () {
	//Route::get('/', 'AddonsController@index');
	Route::post('/update-client-addon', 'AddonsController@UpdateClientAddon')->name('UpdateClientAddon')->middleware('permissionapi:UpdateClientAddon,admin');
});

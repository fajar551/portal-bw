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

Route::namespace('API\Affiliates')->group(function () {
	Route::post('GetAffiliates', 'AffiliatesController@GetAffiliates')->name('GetAffiliates')->middleware('permissionapi:GetAffiliates,admin');
	Route::post('AffiliateActivate', 'AffiliatesController@AffiliateActivate')->name('AffiliateActivate')->middleware('permissionapi:AffiliateActivate,admin');
});

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

Route::namespace('API\Service')->group(function () {
	Route::post('ModuleCreate', 'ServiceController@ModuleCreate')->name('ModuleCreate')->middleware('permissionapi:ModuleCreate,admin');
	Route::post('ModuleTerminate', 'ServiceController@ModuleTerminate')->name('ModuleTerminate')->middleware('permissionapi:ModuleTerminate,admin');
	Route::post('ModuleSuspend', 'ServiceController@ModuleSuspend')->name('ModuleSuspend')->middleware('permissionapi:ModuleSuspend,admin');
	Route::post('ModuleUnsuspend', 'ServiceController@ModuleUnsuspend')->name('ModuleUnsuspend')->middleware('permissionapi:ModuleUnsuspend,admin');
	Route::post('ModuleCustom', 'ServiceController@ModuleCustom')->name('ModuleCustom')->middleware('permissionapi:ModuleCustom,admin');
	Route::post('ModuleChangePackage', 'ServiceController@ModuleChangePackage')->name('ModuleChangePackage')->middleware('permissionapi:ModuleChangePackage,admin');
	Route::post('ModuleChangePw', 'ServiceController@ModuleChangePw')->name('ModuleChangePw')->middleware('permissionapi:ModuleChangePw,admin');
	Route::post('UpgradeProduct', 'ServiceController@UpgradeProduct')->name('UpgradeProduct')->middleware('permissionapi:UpgradeProduct,admin', 'api');
	Route::post('UpdateClientProduct', 'ServiceController@UpdateClientProduct')->name('UpdateClientProduct')->middleware('permissionapi:UpdateClientProduct,admin');
});

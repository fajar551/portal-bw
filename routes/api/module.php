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

Route::namespace('API\Module')->group(function () {
	Route::post('ActivateModule', 'ModuleController@ActivateModule')->name('ActivateModule')->middleware('permissionapi:ActivateModule,admin');
	Route::post('DeactivateModule', 'ModuleController@DeactivateModule')->name('DeactivateModule')->middleware('permissionapi:DeactivateModule,admin');
	Route::post('GetModuleConfigurationParameters', 'ModuleController@GetModuleConfigurationParameters')->name('GetModuleConfigurationParameters')->middleware('permissionapi:GetModuleConfigurationParameters,admin');
	Route::post('UpdateModuleConfiguration', 'ModuleController@UpdateModuleConfiguration')->name('UpdateModuleConfiguration')->middleware('permissionapi:UpdateModuleConfiguration,admin');
});

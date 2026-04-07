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

Route::namespace('API\System')->group(function () {
	//Route::get('/', 'SystemController@index');
	Route::post('/AddBannedIp', 'SystemController@AddBannedIp')->name('AddBannedIp')->middleware('permissionapi:AddBannedIp,admin');
	Route::post('/DecryptPassword', 'SystemController@DecryptPassword')->name('DecryptPassword')->middleware('permissionapi:DecryptPassword,admin');
	Route::post('/EncryptPassword', 'SystemController@EncryptPassword')->name('EncryptPassword')->middleware('permissionapi:EncryptPassword,admin');
	Route::post('/GetActivityLog', 'SystemController@GetActivityLog')->name('GetActivityLog')->middleware('permissionapi:GetActivityLog,admin');
	Route::post('/GetAdminDetails', 'SystemController@GetAdminDetails')->name('GetAdminDetails')->middleware('permissionapi:GetAdminDetails,admin');
	Route::post('/GetAdminUsers', 'SystemController@GetAdminUsers')->name('GetAdminUsers')->middleware('permissionapi:GetAdminUsers,admin');
	Route::post('/GetAutomationLog', 'SystemController@GetAutomationLog')->name('GetAutomationLog')->middleware('permissionapi:GetAutomationLog,admin');
	Route::post('/GetConfigurationValue', 'SystemController@GetConfigurationValue')->name('GetConfigurationValue')->middleware('permissionapi:GetConfigurationValue,admin');
	Route::post('/GetCurrencies', 'SystemController@GetCurrencies')->name('GetCurrencies')->middleware('permissionapi:GetCurrencies,admin');
	Route::post('/GetEmailTemplates', 'SystemController@GetEmailTemplates')->name('GetEmailTemplates')->middleware('permissionapi:GetEmailTemplates,admin');
	Route::post('/GetPaymentMethods', 'SystemController@GetPaymentMethods')->name('GetPaymentMethods')->middleware('permissionapi:GetPaymentMethods,admin');
	Route::post('/GetStaffOnline', 'SystemController@GetStaffOnline')->name('GetStaffOnline')->middleware('permissionapi:GetStaffOnline,admin');
	Route::post('/GetStats', 'SystemController@GetStats')->name('GetStats')->middleware('permissionapi:GetStats,admin');
	Route::post('/GetToDoItems', 'SystemController@GetToDoItems')->name('GetToDoItems')->middleware('permissionapi:GetToDoItems,admin');
	Route::post('/GetToDoItemStatuses', 'SystemController@GetToDoItemStatuses')->name('GetToDoItemStatuses')->middleware('permissionapi:GetToDoItemStatuses,admin');
	Route::post('/GetToDoItemStatuses', 'SystemController@GetToDoItemStatuses')->name('GetToDoItemStatuses')->middleware('permissionapi:GetToDoItemStatuses,admin');
	Route::post('/LogActivity', 'SystemController@LogActivity')->name('LogActivity')->middleware('permissionapi:LogActivity,admin');
	//lanjut
	Route::post('/SendAdminEmail', 'SystemController@SendAdminEmail')->name('SendAdminEmail')->middleware('permissionapi:SendAdminEmail,admin');
	Route::post('/SendEmail', 'SystemController@SendEmail')->name('SendEmail')->middleware('permissionapi:SendEmail,admin');
	Route::post('/SetConfigurationValue', 'SystemController@SetConfigurationValue')->name('SetConfigurationValue')->middleware('permissionapi:SetConfigurationValue,admin');
	Route::post('/TriggerNotificationEvent', 'SystemController@TriggerNotificationEvent')->name('TriggerNotificationEvent')->middleware('permissionapi:TriggerNotificationEvent,admin');

	Route::post('/UpdateAnnouncement', 'SystemController@UpdateAnnouncement')->name('UpdateAnnouncement')->middleware('permissionapi:UpdateAnnouncement,admin');
	Route::post('/UpdateToDoItem', 'SystemController@UpdateToDoItem')->name('UpdateToDoItem')->middleware('permissionapi:UpdateToDoItem,admin');
	Route::post('/UpdateAdminNotes', 'SystemController@UpdateAdminNotes')->name('UpdateAdminNotes')->middleware('permissionapi:UpdateAdminNotes,admin');
});

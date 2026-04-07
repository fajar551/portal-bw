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

Route::namespace('API\Support')->group(function () {
	Route::post('AddAnnouncement', 'SupportController@AddAnnouncement')->name('AddAnnouncement')->middleware('permissionapi:AddAnnouncement,admin');
	Route::post('DeleteAnnouncement', 'SupportController@DeleteAnnouncement')->name('DeleteAnnouncement')->middleware('permissionapi:DeleteAnnouncement,admin');
	Route::post('AddClientNote', 'SupportController@AddClientNote')->name('AddClientNote')->middleware('permissionapi:AddClientNote,admin');
	Route::post('GetAnnouncements', 'SupportController@GetAnnouncements')->name('GetAnnouncements')->middleware('permissionapi:GetAnnouncements,admin');
	Route::post('DeleteTicketNote', 'SupportController@DeleteTicketNote')->name('DeleteTicketNote')->middleware('permissionapi:DeleteTicketNote,admin');
	Route::post('OpenTicket', 'SupportController@OpenTicket')->name('OpenTicket')->middleware('permissionapi:OpenTicket,admin');
	Route::post('AddTicketNote', 'SupportController@AddTicketNote')->name('AddTicketNote')->middleware('permissionapi:AddTicketNote,admin');
	Route::post('AddTicketReply', 'SupportController@AddTicketReply')->name('AddTicketReply')->middleware('permissionapi:AddTicketReply,admin');
	Route::post('DeleteTicket', 'SupportController@DeleteTicket')->name('DeleteTicket')->middleware('permissionapi:DeleteTicket,admin');
	Route::post('DeleteTicketReply', 'SupportController@DeleteTicketReply')->name('DeleteTicketReply')->middleware('permissionapi:DeleteTicketReply,admin');
	Route::post('UpdateTicketReply', 'SupportController@UpdateTicketReply')->name('UpdateTicketReply')->middleware('permissionapi:UpdateTicketReply,admin');
	Route::post('UpdateTicket', 'SupportController@UpdateTicket')->name('UpdateTicket')->middleware('permissionapi:UpdateTicket,admin');
	Route::post('AddCancelRequest', 'SupportController@AddCancelRequest')->name('AddCancelRequest')->middleware('permissionapi:AddCancelRequest,admin');
	Route::post('BlockTicketSender', 'SupportController@BlockTicketSender')->name('BlockTicketSender')->middleware('permissionapi:BlockTicketSender,admin');
	Route::post('MergeTicket', 'SupportController@MergeTicket')->name('MergeTicket')->middleware('permissionapi:MergeTicket,admin');
});

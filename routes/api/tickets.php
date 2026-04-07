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

Route::namespace('API\Tickets')->group(function () {
	Route::post('GetSupportDepartments', 'TicketsController@GetSupportDepartments')->name('GetSupportDepartments')->middleware('permissionapi:GetSupportDepartments,admin');
	Route::post('GetSupportStatuses', 'TicketsController@GetSupportStatuses')->name('GetSupportStatuses')->middleware('permissionapi:GetSupportStatuses,admin');
	Route::post('GetTicketNotes', 'TicketsController@GetTicketNotes')->name('GetTicketNotes')->middleware('permissionapi:GetTicketNotes,admin');
	Route::post('GetTicket', 'TicketsController@GetTicket')->name('GetTicket')->middleware('permissionapi:GetTicket,admin');
	Route::post('GetTicketPredefinedReplies', 'TicketsController@GetTicketPredefinedReplies')->name('GetTicketPredefinedReplies')->middleware('permissionapi:GetTicketPredefinedReplies,admin');
	Route::post('GetTicketPredefinedCats', 'TicketsController@GetTicketPredefinedCats')->name('GetTicketPredefinedCats')->middleware('permissionapi:GetTicketPredefinedCats,admin');
	Route::post('GetTicketAttachment', 'TicketsController@GetTicketAttachment')->name('GetTicketAttachment')->middleware('permissionapi:GetTicketAttachment,admin');
	Route::post('GetTickets', 'TicketsController@GetTickets')->name('GetTickets')->middleware('permissionapi:GetTickets,admin');
	Route::post('GetTicketCounts', 'TicketsController@GetTicketCounts')->name('GetTicketCounts')->middleware('permissionapi:GetTicketCounts,admin');
});

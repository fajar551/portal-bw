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

Route::namespace('API\Billing')->group(function () {
	Route::post('GetCredits', 'BillingController@GetCredits')->name('GetCredits')->middleware('permissionapi:GetCredits,admin');
	Route::post('GetInvoice', 'BillingController@GetInvoice')->name('GetInvoice')->middleware('permissionapi:GetInvoice,admin');
	Route::post('GetInvoices', 'BillingController@GetInvoices')->name('GetInvoices')->middleware('permissionapi:GetInvoices,admin');
	Route::post('GetPayMethods', 'BillingController@GetPayMethods')->name('GetPayMethods')->middleware('permissionapi:GetPayMethods,admin');
	Route::post('GetQuotes', 'BillingController@GetQuotes')->name('GetQuotes')->middleware('permissionapi:GetQuotes,admin');
	Route::post('GetTransactions', 'BillingController@GetTransactions')->name('GetTransactions')->middleware('permissionapi:GetTransactions,admin');
	Route::post('AddBillableItem', 'BillingController@AddBillableItem')->name('AddBillableItem')->middleware('permissionapi:AddBillableItem,admin');
	Route::post('AddCredit', 'BillingController@AddCredit')->name('AddCredit')->middleware('permissionapi:AddCredit,admin');
	Route::post('AddInvoicePayment', 'BillingController@AddInvoicePayment')->name('AddInvoicePayment')->middleware('permissionapi:AddInvoicePayment,admin');
	Route::post('DeleteQuote', 'BillingController@DeleteQuote')->name('DeleteQuote')->middleware('permissionapi:DeleteQuote,admin');
	Route::post('UpdateTransaction', 'BillingController@UpdateTransaction')->name('UpdateTransaction')->middleware('permissionapi:UpdateTransaction,admin');
	Route::post('UpdateQuote', 'BillingController@UpdateQuote')->name('UpdateQuote')->middleware('permissionapi:UpdateQuote,admin');
	Route::post('AddPayMethod', 'BillingController@AddPayMethod')->name('AddPayMethod')->middleware('permissionapi:AddPayMethod,admin');
	Route::post('AddTransaction', 'BillingController@AddTransaction')->name('AddTransaction')->middleware('permissionapi:AddTransaction,admin');
	Route::post('CreateInvoice', 'BillingController@CreateInvoice')->name('CreateInvoice')->middleware('permissionapi:CreateInvoice,admin');
	Route::post('DeletePayMethod', 'BillingController@DeletePayMethod')->name('DeletePayMethod')->middleware('permissionapi:DeletePayMethod,admin');
	Route::post('ApplyCredit', 'BillingController@ApplyCredit')->name('ApplyCredit')->middleware('permissionapi:ApplyCredit,admin');
	Route::post('UpdateInvoice', 'BillingController@UpdateInvoice')->name('UpdateInvoice')->middleware('permissionapi:UpdateInvoice,admin');
	Route::post('AcceptQuote', 'BillingController@AcceptQuote')->name('AcceptQuote')->middleware('permissionapi:AcceptQuote,admin');
});

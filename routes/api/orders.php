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

Route::namespace('API\Orders')->group(function () {
	Route::post('GetOrders', 'OrdersController@GetOrders')->name('GetOrders');
	Route::post('GetOrderStatuses', 'OrdersController@GetOrderStatuses')->name('GetOrderStatuses');
	Route::post('GetPromotions', 'OrdersController@GetPromotions')->name('GetPromotions')->middleware('cors');
	Route::post('PendingOrder', 'OrdersController@PendingOrder')->name('PendingOrder');
	Route::post('GetProducts', 'OrdersController@GetProducts')->name('GetProducts')->middleware('cors');
	Route::post('CancelOrder', 'OrdersController@CancelOrder')->name('CancelOrder');
	Route::post('DeleteOrder', 'OrdersController@DeleteOrder')->name('DeleteOrder');
	Route::post('FraudOrder', 'OrdersController@FraudOrder')->name('FraudOrder');
	Route::post('AcceptOrder', 'OrdersController@AcceptOrder')->name('AcceptOrder');
	Route::post('AddOrder', 'OrdersController@AddOrder')->name('AddOrder');
	Route::post('OrderFraudCheck', 'OrdersController@OrderFraudCheck')->name('OrderFraudCheck');
});

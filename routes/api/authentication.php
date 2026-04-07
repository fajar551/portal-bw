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

Route::namespace('API\Authentication')->group(function () {
	Route::post('login', 'AuthenticationController@login');
	Route::post('register', 'AuthenticationController@register');
	Route::post('forgot-password', 'AuthenticationController@forgotPassword');
	Route::middleware('auth:sanctum')->group(function () {
		Route::get('whoami', 'AuthenticationController@whoami');
	});

	Route::post('ListOAuthCredentials', 'AuthenticationController@ListOAuthCredentials')->name('ListOAuthCredentials')->middleware('permissionapi:ListOAuthCredentials,admin');
	Route::post('DeleteOAuthCredential', 'AuthenticationController@DeleteOAuthCredential')->name('DeleteOAuthCredential')->middleware('permissionapi:DeleteOAuthCredential,admin');
	Route::post('ValidateLogin', 'AuthenticationController@ValidateLogin')->name('ValidateLogin')->middleware('permissionapi:ValidateLogin,admin');
});

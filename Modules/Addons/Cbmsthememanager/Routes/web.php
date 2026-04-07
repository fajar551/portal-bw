<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('cbmsthememanager')->group(function() {
    Route::get('/', 'CbmsthememanagerController@index');
    Route::post('/addNew', 'CbmsthememanagerController@addNew');
    Route::post('/sync', 'CbmsthememanagerController@sync');
    Route::post('/delete', 'CbmsthememanagerController@delete');
    Route::post('/download', 'CbmsthememanagerController@download');
    Route::get('/download', 'CbmsthememanagerController@downloadTheme');
    Route::post('/upload', 'CbmsthememanagerController@upload');
});

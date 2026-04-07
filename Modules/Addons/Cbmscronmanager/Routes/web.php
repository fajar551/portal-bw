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

Route::prefix('cbmscronmanager')->group(function() {
    Route::get('/download', 'CbmscronmanagerController@download');
    Route::get('/remove', 'CbmscronmanagerController@remove');
    Route::post('/addNew', 'CbmscronmanagerController@addNew');
    Route::post('/upload', 'CbmscronmanagerController@upload');
});

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

Route::prefix('cbmshookcallbackmanager')->group(function() {
    Route::get('/download', 'CbmshookcallbackmanagerController@download');
    Route::get('/remove', 'CbmshookcallbackmanagerController@remove');
    Route::post('/addNew', 'CbmshookcallbackmanagerController@addNew');
    Route::post('/upload', 'CbmshookcallbackmanagerController@upload');
});

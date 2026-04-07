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

Route::prefix('cbmsmodulemanager')->name('cbmsmodulemanager.')->group(function() {
    Route::post('save', 'CbmsModuleManagerController@save')->name('save');
    Route::post('addNew', 'CbmsModuleManagerController@addNew')->name('addNew');
    Route::post('syncStatus', 'CbmsModuleManagerController@syncStatus')->name('syncStatus');
    Route::post('removeModule', 'CbmsModuleManagerController@removeModule')->name('removeModule');
    Route::post('downloadModule', 'CbmsModuleManagerController@downloadModule')->name('downloadModule');
    Route::get('download/{module}', 'CbmsModuleManagerController@download')->name('download');
});

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

Route::namespace('API\ProjectManagement')->group(function () {
	Route::post('/AddProjectMessage', 'ProjectManagementController@AddProjectMessage')->name('AddProjectMessage')->middleware('permissionapi:AddProjectMessage,admin');
	Route::post('/AddProjectTask', 'ProjectManagementController@AddProjectTask')->name('AddProjectTask')->middleware('permissionapi:AddProjectTask,admin');
	Route::post('/CreateProject', 'ProjectManagementController@CreateProject')->name('CreateProject')->middleware('permissionapi:CreateProject,admin');
	Route::post('/DeleteProjectTask', 'ProjectManagementController@DeleteProjectTask')->name('DeleteProjectTask')->middleware('permissionapi:DeleteProjectTask,admin');
	Route::post('/StartTaskTimer', 'ProjectManagementController@StartTaskTimer')->name('StartTaskTimer')->middleware('permissionapi:StartTaskTimer,admin');
	Route::post('/EndTaskTimer', 'ProjectManagementController@EndTaskTimer')->name('EndTaskTimer')->middleware('permissionapi:EndTaskTimer,admin');
	Route::post('/GetProject', 'ProjectManagementController@GetProject')->name('GetProject')->middleware('permissionapi:GetProject,admin');
	Route::post('/GetProjects', 'ProjectManagementController@GetProjects')->name('GetProjects')->middleware('permissionapi:GetProjects,admin');
	Route::post('/UpdateProject', 'ProjectManagementController@UpdateProject')->name('UpdateProject')->middleware('permissionapi:UpdateProject,admin');
	Route::post('/UpdateProjectTask', 'ProjectManagementController@UpdateProjectTask')->name('UpdateProjectTask')->middleware('permissionapi:UpdateProjectTask,admin');
});

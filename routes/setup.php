<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

/**
 * Setup Wizard.
 */
Route::group(['middleware' => 'setup'], function () {
    Route::any('/', 'SetupController@welcome');
    Route::any('/database', 'SetupController@database');
    Route::get('/info', 'SetupController@info');
    Route::post('/finish', 'SetupController@finish');
});

Route::group(['middleware' => ['authorize', App\Http\Middleware\LockUpdatePage::class]], function () {
    Route::any('/update', 'SetupController@update');
    Route::any('/exec-update', 'SetupController@doUpdate');
    Route::view('/changelog', 'setup.updates.changelog');
});

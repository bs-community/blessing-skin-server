<?php

Route::any('', 'HomeController@apiRoot');

Route::prefix('auth')->group(function () {
    Route::post('login', 'AuthController@jwtLogin');
    Route::post('logout', 'AuthController@jwtLogout')->middleware('auth:jwt');
    Route::post('refresh', 'AuthController@jwtRefresh')->middleware('auth:jwt');
});

Route::prefix('user')->middleware('auth:jwt,oauth')->group(function () {
    Route::get('', 'UserController@user');

    Route::get('notifications', 'NotificationsController@all');
    Route::get('notifications/{id}', 'NotificationsController@read');
});

Route::prefix('players')->middleware('auth:jwt,oauth')->group(function () {
    Route::get('', 'PlayerController@list');
    Route::post('', 'PlayerController@add');
    Route::delete('{pid}', 'PlayerController@delete');
    Route::put('{pid}/name', 'PlayerController@rename');
    Route::put('{pid}/textures', 'PlayerController@setTexture');
    Route::delete('{pid}/textures', 'PlayerController@clearTexture');
});

Route::prefix('closet')->middleware('auth:jwt,oauth')->group(function () {
    Route::get('', 'ClosetController@getClosetData');
    Route::post('', 'ClosetController@add');
    Route::put('{tid}', 'ClosetController@rename');
    Route::delete('{tid}', 'ClosetController@remove');
});

Route::prefix('admin')
    ->middleware(['auth:jwt,oauth', 'role:admin'])
    ->group(function () {
        Route::prefix('users')->group(function () {
            Route::get('', 'UsersManagementController@list')->name('list');
            Route::prefix('{user}')->group(function () {
                Route::put('email', 'UsersManagementController@email')->name('email');
                Route::put('verification', 'UsersManagementController@verification')->name('verification');
                Route::put('nickname', 'UsersManagementController@nickname')->name('nickname');
                Route::put('password', 'UsersManagementController@password')->name('password');
                Route::put('score', 'UsersManagementController@score')->name('score');
                Route::put('permission', 'UsersManagementController@permission')->name('permission');
                Route::delete('', 'UsersManagementController@delete')->name('delete');
            });
        });

        Route::prefix('players')->group(function () {
            Route::get('', 'PlayersManagementController@list');
            Route::put('{player}/name', 'PlayersManagementController@name');
            Route::put('{player}/owner', 'PlayersManagementController@owner');
            Route::put('{player}/textures', 'PlayersManagementController@texture');
            Route::delete('{player}', 'PlayersManagementController@delete');
        });

        Route::prefix('closet')->group(function () {
            Route::get('{uid}', 'ClosetManagementController@list');
            Route::post('{uid}', 'ClosetManagementController@add');
            Route::delete('{uid}', 'ClosetManagementController@remove');
        });

        Route::prefix('reports')->group(function () {
            Route::get('', 'ReportController@manage');
            Route::put('{report}', 'ReportController@review');
        });

        Route::post('notifications', 'NotificationsController@send');
    });

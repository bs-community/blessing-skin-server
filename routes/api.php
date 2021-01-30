<?php

Route::any('', 'HomeController@apiRoot');

Route::prefix('user')->middleware('auth:oauth')->group(function () {
    Route::get('', 'UserController@user');

    Route::get('notifications', 'NotificationsController@all');
    Route::post('notifications/{id}', 'NotificationsController@read');
});

Route::prefix('players')->middleware('auth:oauth')->group(function () {
    Route::get('', 'PlayerController@list');
    Route::post('', 'PlayerController@add');
    Route::delete('{player}', 'PlayerController@delete');
    Route::put('{player}/name', 'PlayerController@rename');
    Route::put('{player}/textures', 'PlayerController@setTexture');
    Route::delete('{player}/textures', 'PlayerController@clearTexture');
});

Route::prefix('closet')->middleware('auth:oauth')->group(function () {
    Route::get('', 'ClosetController@getClosetData');
    Route::post('', 'ClosetController@add');
    Route::put('{tid}', 'ClosetController@rename');
    Route::delete('{tid}', 'ClosetController@remove');
});

Route::prefix('admin')
    ->middleware(['auth:oauth', 'role:admin'])
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
            Route::get('{user}', 'ClosetManagementController@list');
            Route::post('{user}', 'ClosetManagementController@add');
            Route::delete('{user}', 'ClosetManagementController@remove');
        });

        Route::prefix('reports')->group(function () {
            Route::get('', 'ReportController@manage');
            Route::put('{report}', 'ReportController@review');
        });

        Route::post('notifications', 'NotificationsController@send');
    });

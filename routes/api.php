<?php

Route::any('', 'HomeController@apiRoot');

Route::prefix('user')->middleware('auth:oauth')->group(function () {
    Route::get('', 'UserController@user')->middleware(['scope:User.Read']);

    Route::middleware(['scope:Notification.Read'])->group(function () {
        Route::get('notifications', 'NotificationsController@all');
        Route::post('notifications/{id}', 'NotificationsController@read');
    });
});

Route::prefix('players')->middleware('auth:oauth')->group(function () {
    Route::get('', 'PlayerController@list')->middleware(['scope:Player.Read,Player.ReadWrite']);

    Route::middleware(['scope:Player.ReadWrite'])->group(function () {
        Route::post('', 'PlayerController@add');
        Route::delete('{player}', 'PlayerController@delete');
        Route::put('{player}/name', 'PlayerController@rename');
        Route::put('{player}/textures', 'PlayerController@setTexture');
        Route::delete('{player}/textures', 'PlayerController@clearTexture');
    });
});

Route::prefix('closet')->middleware('auth:oauth')->group(function () {
    Route::get('', 'ClosetController@getClosetData')->middleware(['scope:Closet.Read,Closet.ReadWrite']);

    Route::middleware(['scope:Closet.ReadWrite'])->group(function () {
        Route::post('', 'ClosetController@add');
        Route::put('{tid}', 'ClosetController@rename');
        Route::delete('{tid}', 'ClosetController@remove');
    });
});

Route::prefix('admin')
    ->middleware(['auth:oauth', 'role:admin'])
    ->group(function () {
        Route::prefix('users')->group(function () {
            Route::get('', 'UsersManagementController@list')->name('list')->middleware(['scope:UsersManagement.Read,UsersManagement.ReadWrite']);
            Route::prefix('{user}')->middleware(['scope:UsersManagement.ReadWrite'])->group(function () {
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
            Route::get('', 'PlayersManagementController@list')->middleware(['scope:PlayersManagement.Read,PlayersManagement.ReadWrite']);

            Route::middleware(['scope:PlayersManagement.ReadWrite'])->group(function () {
                Route::put('{player}/name', 'PlayersManagementController@name');
                Route::put('{player}/owner', 'PlayersManagementController@owner');
                Route::put('{player}/textures', 'PlayersManagementController@texture');
                Route::delete('{player}', 'PlayersManagementController@delete');
            });
        });

        Route::prefix('closet')->group(function () {
            Route::get('{user}', 'ClosetManagementController@list')->middleware(['scope:ClosetManagement.Read,ClosetManagement.ReadWrite']);
            Route::middleware(['scope:ClosetManagement.ReadWrite'])->group(function () {
                Route::post('{user}', 'ClosetManagementController@add');
                Route::delete('{user}', 'ClosetManagementController@remove');
            });
        });

        Route::prefix('reports')->group(function () {
            Route::get('', 'ReportController@manage')->middleware(['scope:ReportsManagement.Read,ReportsManagement.ReadWrite']);
            Route::put('{report}', 'ReportController@review')->middleware(['scope:ReportsManagement.ReadWrite']);
        });

        Route::post('notifications', 'NotificationsController@send')->middleware(['scope:Notification.ReadWrite']);
    });

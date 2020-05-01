<?php

Route::any('', 'HomeController@apiRoot');

Route::prefix('auth')->group(function () {
    Route::post('login', 'AuthController@jwtLogin');
    Route::post('logout', 'AuthController@jwtLogout')->middleware('auth:jwt');
    Route::post('refresh', 'AuthController@jwtRefresh')->middleware('auth:jwt');
});

Route::prefix('user')->middleware('auth:jwt,oauth')->group(function () {
    Route::get('', 'UserController@user');
});

Route::prefix('players')->middleware('auth:jwt,oauth')->group(function () {
    Route::get('', 'PlayerController@listAll');
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
        Route::prefix('players')->group(function () {
            Route::get('', 'PlayersManagementController@list');
            Route::put('{player}/name', 'PlayersManagementController@name');
            Route::put('{player}/owner', 'PlayersManagementController@owner');
            Route::put('{player}/textures', 'PlayersManagementController@texture');
            Route::delete('{player}', 'PlayersManagementController@delete');
        });
    });

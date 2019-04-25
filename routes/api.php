<?php

Route::prefix('auth')->group(function () {
    Route::post('login', 'AuthController@jwtLogin');
    Route::post('logout', 'AuthController@jwtLogout')->middleware('auth:jwt');
    Route::post('refresh', 'AuthController@jwtRefresh')->middleware('auth:jwt');
});

Route::prefix('user')->middleware('auth:jwt,oauth')->group(function () {
    Route::put('sign', 'UserController@sign');
});

Route::prefix('players')->middleware('auth:jwt,oauth')->group(function () {
    Route::get('', 'PlayerController@listAll');
    Route::post('', 'PlayerController@add');
    Route::delete('{pid}', 'PlayerController@delete');
    Route::put('{pid}/name', 'PlayerController@rename');
    Route::put('{pid}/textures', 'PlayerController@setTexture');
    Route::delete('{pid}/textures', 'PlayerController@clearTexture');
});

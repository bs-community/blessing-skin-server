<?php

Route::prefix('auth')->group(function ($route) {
    $route->post('login', 'AuthController@apiLogin');
    $route->post('logout', 'AuthController@apiLogout')->middleware('auth.jwt');
    $route->post('refresh', 'AuthController@apiRefresh')->middleware('auth.jwt');
});

Route::prefix('user')->middleware('auth.jwt')->group(function ($route) {
    $route->put('sign', 'UserController@sign');

    $route->post('player', 'PlayerController@add');
});


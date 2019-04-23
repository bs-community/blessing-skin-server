<?php

Route::prefix('auth')->group(function ($route) {
    $route->post('login', 'AuthController@apiLogin');
    $route->post('logout', 'AuthController@apiLogout')->middleware('auth.jwt');
});

Route::prefix('user')->middleware('auth.jwt')->group(function ($route) {
    $route->post('sign', 'UserController@sign');
});


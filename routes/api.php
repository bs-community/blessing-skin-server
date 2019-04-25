<?php

Route::prefix('auth')->group(function ($route) {
    $route->post('login', 'AuthController@jwtLogin');
    $route->post('logout', 'AuthController@jwtLogout')->middleware('auth:jwt');
    $route->post('refresh', 'AuthController@jwtRefresh')->middleware('auth:jwt');
});

Route::prefix('user')->middleware('auth:jwt')->group(function ($route) {
    $route->put('sign', 'UserController@sign');

    $route->get('players', 'PlayerController@listAll');
    $route->post('players', 'PlayerController@add');
    $route->delete('players/{pid}', 'PlayerController@delete');
    $route->put('players/{pid}/name', 'PlayerController@rename');
    $route->put('players/{pid}/textures', 'PlayerController@setTexture');
    $route->delete('players/{pid}/textures', 'PlayerController@clearTexture');
});


<?php

Route::prefix('auth')->group(function ($route) {
    $route->post('login', 'AuthController@apiLogin');
    $route->post('logout', 'AuthController@apiLogout')->middleware('auth.jwt');
    $route->post('refresh', 'AuthController@apiRefresh')->middleware('auth.jwt');
});

Route::prefix('user')->middleware('auth:api')->group(function ($route) {
    $route->put('sign', 'UserController@sign');

    $route->get('players', 'PlayerController@listAll');
    $route->post('players', 'PlayerController@add');
    $route->delete('players/{pid}', 'PlayerController@delete');
    $route->put('players/{pid}/name', 'PlayerController@rename');
    $route->put('players/{pid}/textures', 'PlayerController@setTexture');
    $route->delete('players/{pid}/textures', 'PlayerController@clearTexture');
});


<?php

Route::group(['middleware' => 'player'], function () {
    Route::get('/{player}.json', 'TextureController@json');
    Route::get('/csl/{player}.json', 'TextureController@json');
});

Route::get('/textures/{hash}', 'TextureController@texture');

Route::get('/avatar/player/{size}/{name}.png', 'TextureController@avatarByPlayer');
Route::get('/avatar/user/{uid}/{size?}', 'TextureController@avatar');
Route::get('/avatar/{tid}/{size?}', 'TextureController@avatarByTid');

Route::get('/raw/{tid}.png', 'TextureController@raw');

Route::get('/preview/{tid}/{size?}', 'TextureController@preview');

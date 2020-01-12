<?php

Route::get('/{player}.json', 'TextureController@json');
Route::get('/csl/{player}.json', 'TextureController@json');

Route::get('/textures/{hash}', 'TextureController@texture');

Route::get('/avatar/player/{name}', 'TextureController@avatarByPlayer');
Route::get('/avatar/user/{uid}', 'TextureController@avatarByUser');
Route::get('/avatar/{tid}', 'TextureController@avatarByTexture');

Route::get('/raw/{tid}', 'TextureController@raw');

Route::get('/preview/{tid}', 'TextureController@preview');

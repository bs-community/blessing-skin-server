<?php

Route::group(['middleware' => 'player'], function () {
    // Json profile
    Route::get('/{player_name}.json', 'TextureController@json');
    Route::get('/{api}/{player_name}.json', 'TextureController@jsonWithApi')->where('api', 'usm|csl');
    // Legacy links
    Route::get('/skin/{player_name}.png', 'TextureController@skin');
    Route::get('/cape/{player_name}.png', 'TextureController@cape');
});

Route::get('/textures/{hash}', 'TextureController@texture');
Route::get('/{api}/textures/{hash}', 'TextureController@textureWithApi')->where('api', 'usm|csl');

Route::get('/avatar/player/{size}/{name}.png', 'TextureController@avatarByPlayer');
Route::get('/avatar/user/{uid}/{size?}', 'TextureController@avatar');
Route::get('/avatar/{tid}/{size?}', 'TextureController@avatarByTid');

Route::get('/raw/{tid}.png', 'TextureController@raw');

Route::get('/preview/{tid}/{size?}', 'TextureController@preview');

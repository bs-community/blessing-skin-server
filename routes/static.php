<?php

Route::get('{player}.json', 'TextureController@json');
Route::get('csl/{player}.json', 'TextureController@json');

Route::get('textures/{hash}', 'TextureController@texture');
Route::get('raw/{tid}', 'TextureController@raw');

Route::prefix('avatar')->name('avatar.')->group(function () {
    Route::get('player/{name}', 'TextureController@avatarByPlayer')->name('player');
    Route::get('user/{uid}', 'TextureController@avatarByUser')->name('user');
    Route::get('{tid}', 'TextureController@avatarByTexture')->name('texture');
});

Route::get('preview/{tid}', 'TextureController@preview');

<?php

Route::get('{player}.json', 'TextureController@json');
Route::get('csl/{player}.json', 'TextureController@json');

Route::get('textures/{hash}', 'TextureController@texture');
Route::get('csl/textures/{hash}', 'TextureController@texture');
Route::get('raw/{tid}', 'TextureController@raw');

Route::prefix('avatar')->name('avatar.')->group(function () {
    Route::get('player/{name}', 'TextureController@avatarByPlayer')->name('player');
    Route::get('user/{uid}', 'TextureController@avatarByUser')->name('user');
    Route::get('hash/{hash}', 'TextureController@avatarByHash')->name('hash');
    Route::get('{tid}', 'TextureController@avatarByTexture')->name('texture');
});

Route::prefix('preview')->name('preview.')->group(function () {
    Route::get('{texture}', 'TextureController@preview')->name('texture')
        ->middleware(Illuminate\Routing\Middleware\SubstituteBindings::class);
    Route::get('hash/{hash}', 'TextureController@previewByHash')->name('hash');
});

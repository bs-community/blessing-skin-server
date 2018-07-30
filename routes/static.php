<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

/**
 * Resources
 */
Route::group(['middleware' => 'player'], function()
{
    // Json profile
    Route::get('/{player_name}.json',           'TextureController@json');
    Route::get('/{api}/{player_name}.json',     'TextureController@jsonWithApi')->where('api', 'usm|csl');
    // Legacy links
    Route::get('/skin/{player_name}.png',       'TextureController@skin');
    Route::get('/skin/{model}/{pname}.png',     'TextureController@skinWithModel');
    Route::get('/cape/{player_name}.png',       'TextureController@cape');
});

Route::get('/textures/{hash}',                  'TextureController@texture');
Route::get('/{api}/textures/{hash}',            'TextureController@textureWithApi')->where('api', 'usm|csl');

Route::get('/avatar/{base64_email}.png',        'TextureController@avatar');
Route::get('/avatar/{size}/{base64_email}.png', 'TextureController@avatarWithSize');
Route::get('/avatar/{tid}',                     'TextureController@avatarByTid');
Route::get('/avatar/{size}/{tid}',              'TextureController@avatarByTidWithSize');

Route::get('/raw/{tid}.png',                    'TextureController@raw');

Route::get('/preview/{tid}.png',                'TextureController@preview');
Route::get('/preview/{size}/{tid}.png',         'TextureController@previewWithSize');

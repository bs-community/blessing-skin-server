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

Route::get('/',                          'HomeController@index');
Route::get('/index.php',                 'HomeController@index');

/**
 * Auth
 */
Route::group(['prefix' => 'auth'], function()
{
    Route::group(['middleware' =>        'App\Http\Middleware\RedirectIfAuthenticated'], function()
    {
        Route::get ('/login',            'AuthController@login');
        Route::get ('/register',         'AuthController@register');
        Route::get ('/forgot',           'AuthController@forgot');
        Route::get ('/reset',            'AuthController@reset');
    });

    Route::any('/logout',                'AuthController@logout');
    Route::any('/captcha',               'AuthController@captcha');

    Route::group(['middleware' =>        'App\Http\Middleware\CheckPostMiddleware'], function()
    {
        Route::post('/login',            'AuthController@handleLogin');
        Route::post('/register',         'AuthController@handleRegister');
        Route::post('/forgot',           'AuthController@handleForgot');
    });

    Route::post('/reset',                'AuthController@handleReset');
});

/**
 * User Center
 */
Route::group(['middleware' =>            'App\Http\Middleware\CheckAuthenticated', 'prefix' => 'user'], function()
{
    Route::any ('',                      'UserController@index');
    Route::any ('/sign',                 'UserController@sign');

    // Profile
    Route::get ('/profile',              'UserController@profile');
    Route::post('/profile',              'UserController@handleProfile');
    Route::post('/profile/avatar',       'UserController@setAvatar');
    Route::get ('/config',               'UserController@config');

    // Player
    Route::any ('/player',               'PlayerController@index');
    Route::post('/player/add',           'PlayerController@add');
    Route::post('/player/show',          'PlayerController@show');
    Route::post('/player/preference',    'PlayerController@setPreference');
    Route::post('/player/set',           'PlayerController@setTexture');
    Route::post('/player/texture',       'PlayerController@changeTexture');
    Route::post('/player/texture/clear', 'PlayerController@clearTexture');
    Route::post('/player/rename',        'PlayerController@rename');
    Route::post('/player/delete',        'PlayerController@delete');

    // Closet
    Route::get ('/closet',               'ClosetController@index');
    Route::post('/closet/add',           'ClosetController@add');
    Route::post('/closet/remove',        'ClosetController@remove');
});

/**
 * Skin Library
 */
Route::group(['prefix' => 'skinlib'], function()
{
    Route::get ('',                             'SkinlibController@index');
    Route::any ('/info/{tid}',                  'SkinlibController@info');
    Route::any ('/show',                        'SkinlibController@show');
    Route::any ('/search',                      'SkinlibController@search');

    Route::group(['middleware' =>               'App\Http\Middleware\CheckAuthenticated'], function()
    {
        Route::get ('/upload',                  'SkinlibController@upload');
        Route::post('/upload',                  'SkinlibController@handleUpload');

        Route::post('/rename',                  'SkinlibController@rename');
        Route::post('/privacy/{tid}',           'SkinlibController@privacy');
        Route::post('/delete',                  'SkinlibController@delete');
    });
});

/**
 * Admin Panel
 */
Route::group(['middleware' =>                   'App\Http\Middleware\CheckAdminMiddleware', 'prefix' => 'admin'], function()
{
    Route::get('/',                             'AdminController@index');

    Route::any('/customize',                    'AdminController@customize');
    Route::any('/score',                        'AdminController@score');
    Route::any('/options',                      'AdminController@options');
    Route::any('/update',                       'AdminController@update');

    Route::get('/users',                        'AdminController@users');
    Route::get('/players',                      'AdminController@players');
    // ajax handlers
    Route::post('/users',                       'AdminController@userAjaxHandler');
    Route::post('/players',                     'AdminController@playerAjaxHandler');
});

/**
 * Resources
 */
Route::group(['middleware' =>                   'App\Http\Middleware\CheckPlayerExistMiddleware'], function()
{
    // Json profile
    Route::get('/{player_name}.json',           'TextureController@json');
    Route::get('/{api}/{player_name}.json',     'TextureController@jsonWithApi');
    // Legacy links
    Route::get('/skin/{player_name}.png',       'TextureController@skin');
    Route::get('/cape/{player_name}.png',       'TextureController@cape');
});

Route::get('/textures/{hash}',                  'TextureController@texture');
Route::get('/{api}/textures/{hash}',            'TextureController@textureWithApi')->where('api', 'usm|csl');

Route::get('/avatar/{base64_email}.png',        'TextureController@avatar');
Route::get('/avatar/{size}/{base64_email}.png', 'TextureController@avatarWithSize');

Route::get('/raw/{tid}.png',                    'TextureController@raw');

Route::get('/preview/{tid}.png',                'TextureController@preview');
Route::get('/preview/{size}/{tid}.png',         'TextureController@previewWithSize');

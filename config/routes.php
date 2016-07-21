<?php
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
|
*/

use Pecee\SimpleRouter\SimpleRouter as Route;

Route::get('/',                          'HomeController@index');

/**
 * Auth
 */
Route::group(['prefix' => 'auth'], function()
{
    Route::group(['middleware' =>        'App\Middlewares\RedirectIfLoggedInMiddleware'], function()
    {
        Route::get ('/login',            'AuthController@login');
        Route::get ('/register',         'AuthController@register');
        Route::get ('/forgot',           'AuthController@forgot');
        Route::get ('/reset',            'AuthController@reset');
    });

    Route::all('/logout',                'AuthController@logout');
    Route::all('/captcha',               'AuthController@captcha');

    Route::group(['middleware' =>        'App\Middlewares\CheckPostMiddleware'], function()
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
Route::group(['prefix' => 'user',        'middleware' => 'App\Middlewares\CheckLoggedInMiddleware'], function()
{
    Route::all ('',                      'UserController@index');
    Route::all ('/sign',                 'UserController@sign');

    // Profile
    Route::get ('/profile',              'UserController@profile');
    Route::post('/profile',              'UserController@handleProfile');
    Route::post('/profile/avatar',       'UserController@setAvatar');
    Route::get ('/config',               'UserController@config');

    // Player
    Route::all ('/player',               'PlayerController@index');
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
    Route::all ('/info/{tid}',                  'SkinlibController@info');
    Route::all ('/show',                        'SkinlibController@show');
    Route::post('/save',                        'SkinlibController@save');
    Route::all ('/search',                      'SkinlibController@search');

    Route::post('/privacy/{tid}',               'SkinlibController@privacy');

    Route::group(['middleware' => 'App\Middlewares\CheckLoggedInMiddleware'], function()
    {
        Route::get ('/upload',                  'SkinlibController@upload');
        Route::post('/upload',                  'SkinlibController@handleUpload');

        Route::post('/delete',                  'SkinlibController@delete');
    });
});

/**
 * Resources
 */
Route::group(['middleware' => 'App\Middlewares\CheckPlayerExistMiddleware'], function()
{
    // Json profile
    Route::get('/{player_name}.json',           'TextureController@json')->where(['player_name' => '[^\\/]+?']);
    Route::get('/{api}/{player_name}.json',     'TextureController@jsonWithApi')->where(['player_name' => '[^\\/]+?']);
    // Legacy links
    Route::get('/skin/{player_name}.png',       'TextureController@skin');
    Route::get('/cape/{player_name}.png',       'TextureController@cape');
});

Route::get('/avatar/{base64_email}.png',        'TextureController@avatar');
Route::get('/avatar/{size}/{base64_email}.png', 'TextureController@avatarWithSize')->where(['base64_email' => '[^\\/]+?']);

Route::get('/preview/{tid}.png',                'TextureController@preview');
Route::get('/preview/{size}/{tid}.png',         'TextureController@previewWithSize');

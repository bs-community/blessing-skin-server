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

Route::get('/',              'HomeController@index');
Route::get('/index.php',     'HomeController@index');

Route::get('/locale/{lang}', 'HomeController@locale');

/**
 * Auth
 */
Route::group(['prefix' => 'auth'], function ()
{
    Route::group(['middleware' => 'guest'], function ()
    {
        Route::view('/login',        'auth.login');
        Route::get ('/register',    'AuthController@register');
        Route::get ('/forgot',      'AuthController@forgot');
        Route::get ('/reset/{uid}', 'AuthController@reset')->name('auth.reset')->middleware('signed');
    });

    Route::any('/logout',         'AuthController@logout');
    Route::any('/captcha',        '\Mews\Captcha\CaptchaController@getCaptcha');

    Route::post('/login',         'AuthController@handleLogin');
    Route::post('/register',      'AuthController@handleRegister');
    Route::post('/forgot',        'AuthController@handleForgot');
    Route::post('/reset/{uid}',   'AuthController@handleReset')->middleware('signed');

    Route::get ('/verify/{uid}',  'AuthController@verify')->name('auth.verify')->middleware('signed');
});

/**
 * User Center
 */
Route::group(['middleware' => ['web', 'auth'], 'prefix' => 'user'], function ()
{
    Route::any ('',                      'UserController@index');
    Route::get ('/score-info',           'UserController@scoreInfo');
    Route::post('/sign',                 'UserController@sign');

    // Profile
    Route::get ('/profile',              'UserController@profile');
    Route::post('/profile',              'UserController@handleProfile');
    Route::post('/profile/avatar',       'UserController@setAvatar');

    // Email Verification
    Route::post('/email-verification',   'UserController@sendVerificationEmail');

    // Player
    Route::group(['prefix' => 'player', 'middleware' => 'verified'], function () {
        Route::any ('',               'PlayerController@index');
        Route::get ('/list',          'PlayerController@listAll');
        Route::post('/add',           'PlayerController@add');
        Route::any ('/show',          'PlayerController@show');
        Route::post('/preference',    'PlayerController@setPreference');
        Route::post('/set',           'PlayerController@setTexture');
        Route::post('/texture/clear', 'PlayerController@clearTexture');
        Route::post('/rename',        'PlayerController@rename');
        Route::post('/delete',        'PlayerController@delete');
    });

    // Closet
    Route::get ('/closet',               'ClosetController@index');
    Route::get ('/closet-data',          'ClosetController@getClosetData');
    Route::post('/closet/add',           'ClosetController@add');
    Route::post('/closet/remove',        'ClosetController@remove');
    Route::post('/closet/rename',        'ClosetController@rename');
});

/**
 * Skin Library
 */
Route::group(['prefix' => 'skinlib'], function ()
{
    Route::get('',                    'SkinlibController@index');
    Route::any('/info/{tid}',         'SkinlibController@info');
    Route::any('/show/{tid}',         'SkinlibController@show');
    Route::any('/data',               'SkinlibController@getSkinlibFiltered');

    Route::group(['middleware' => ['auth', 'verified']], function ()
    {
        Route::get ('/upload',        'SkinlibController@upload');
        Route::post('/upload',        'SkinlibController@handleUpload');
        Route::post('/model',         'SkinlibController@model');
        Route::post('/rename',        'SkinlibController@rename');
        Route::post('/privacy',       'SkinlibController@privacy');
        Route::post('/delete',        'SkinlibController@delete');
    });
});

/**
 * Admin Panel
 */
Route::group(['middleware' => ['auth', 'admin'], 'prefix' => 'admin'], function ()
{
    Route::get('/',            'AdminController@index');

    Route::any('/customize',   'AdminController@customize');
    Route::any('/score',       'AdminController@score');
    Route::any('/options',     'AdminController@options');

    Route::view('/users',       'admin.users');
    Route::any ('/user-data',   'AdminController@getUserData');

    Route::view('/players',     'admin.players');
    Route::any ('/player-data', 'AdminController@getPlayerData');
    Route::get ('/user/{uid}',  'AdminController@getOneUser');

    // ajax handlers
    Route::post('/users',      'AdminController@userAjaxHandler');
    Route::post('/players',    'AdminController@playerAjaxHandler');

    Route::group(['prefix' => 'plugins', 'middleware' => 'super-admin'], function () {
        Route::get ('/data',   'PluginController@getPluginData');

        Route::view('/manage', 'admin.plugins');
        Route::post('/manage', 'PluginController@manage');
        Route::any ('/config/{name}', 'PluginController@config');

        Route::view('/market', 'admin.market');
        Route::get ('/market-data', 'MarketController@marketData');
        Route::get ('/market/check', 'MarketController@checkUpdates');
        Route::post('/market/download', 'MarketController@download');
    });

    Route::group(['prefix' => 'update', 'middleware' => 'super-admin'], function () {
        Route::any('',          'UpdateController@showUpdatePage');
        Route::get('/check',    'UpdateController@checkUpdates');
        Route::any('/download', 'UpdateController@download');
    });
});

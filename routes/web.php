<?php

use App\Http\Middleware;

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

Route::get('', 'HomeController@index');

Route::prefix('auth')->name('auth.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', 'AuthController@login')->name('login');
        Route::post('login', 'AuthController@handleLogin')->name('login');

        Route::get('register', 'AuthController@register')->name('register');
        Route::post('register', 'AuthController@handleRegister')->name('register');

        Route::get('forgot', 'AuthController@forgot')->name('forgot');
        Route::post('forgot', 'AuthController@handleForgot')->name('forgot');

        Route::middleware('signed')->name('reset')->group(function () {
            Route::get('reset/{uid}', 'AuthController@reset');
            Route::post('reset/{uid}', 'AuthController@handleReset');
        });

        Route::get('login/{driver}', 'AuthController@oauthLogin');
        Route::get('login/{driver}/callback', 'AuthController@oauthCallback');
    });

    Route::post('logout', 'AuthController@logout')->name('logout')->middleware('authorize');
    Route::any('captcha', 'AuthController@captcha');

    Route::middleware(['authorize', Middleware\EnsureEmailFilled::class])
        ->name('bind')
        ->group(function () {
            Route::view('bind', 'auth.bind');
            Route::post('bind', 'AuthController@fillEmail');
        });

    Route::get('verify/{uid}', 'AuthController@verify')->name('verify')->middleware('signed');
});

Route::prefix('user')
    ->name('user.')
    ->middleware(['authorize', Middleware\RequireBindPlayer::class])
    ->group(function () {
        Route::get('', 'UserController@index')->name('home');
        Route::get('notifications/{id}', 'UserController@readNotification')->name('notification');
        Route::get('score-info', 'UserController@scoreInfo')->name('score');
        Route::post('sign', 'UserController@sign')->name('sign');

        Route::get('reports', 'ReportController@track');

        Route::prefix('profile')->group(function () {
            Route::get('', 'UserController@profile')->name('profile');
            Route::post('', 'UserController@handleProfile')->name('profile');
            Route::post('avatar', 'UserController@setAvatar')->name('profile.avatar');
        });

        Route::post('email-verification', 'UserController@sendVerificationEmail');

        Route::prefix('player')
            ->name('player.')
            ->middleware('verified')
            ->group(function () {
                Route::get('', 'PlayerController@index')->name('page');
                Route::get('list', 'PlayerController@listAll')->name('list');
                Route::post('add', 'PlayerController@add')->name('add');
                Route::post('set/{pid}', 'PlayerController@setTexture')->name('set');
                Route::post('texture/clear/{pid}', 'PlayerController@clearTexture')->name('clear');
                Route::post('rename/{pid}', 'PlayerController@rename')->name('rename');
                Route::post('delete/{pid}', 'PlayerController@delete')->name('delete');
                Route::view('bind', 'user.bind')->name('bind');
                Route::post('bind', 'PlayerController@bind')->name('bind');
            });

        Route::prefix('closet')->name('closet.')->group(function () {
            Route::get('', 'ClosetController@index')->name('page');
            Route::get('list', 'ClosetController@getClosetData')->name('list');
            Route::get('ids', 'ClosetController@allIds')->name('ids');
            Route::post('add', 'ClosetController@add')->name('add');
            Route::post('remove/{tid}', 'ClosetController@remove')->name('remove');
            Route::post('rename/{tid}', 'ClosetController@rename')->name('rename');
        });

        // OAuth2 Management
        Route::view('oauth/manage', 'user.oauth')->middleware('verified');
    });

Route::prefix('skinlib')->name('skinlib.')->group(function () {
    Route::view('', 'skinlib.index')->name('home');
    Route::get('info/{tid}', 'SkinlibController@info')->name('info');
    Route::get('show/{tid}', 'SkinlibController@show')->name('show');
    Route::get('list', 'SkinlibController@library')->name('list');

    Route::middleware(['authorize', 'verified'])->group(function () {
        Route::prefix('upload')->name('upload')->group(function () {
            Route::get('', 'SkinlibController@upload');
            Route::post('', 'SkinlibController@handleUpload');
        });
        Route::post('model', 'SkinlibController@model');
        Route::post('rename', 'SkinlibController@rename');
        Route::post('privacy', 'SkinlibController@privacy');
        Route::post('delete', 'SkinlibController@delete');
        Route::post('report', 'ReportController@submit');
    });
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['authorize', 'role:admin'])
    ->group(function () {
        Route::get('', 'AdminController@index');
        Route::get('chart', 'AdminController@chartData');
        Route::post('notifications/send', 'AdminController@sendNotification');

        Route::any('customize', 'AdminController@customize');
        Route::any('score', 'AdminController@score');
        Route::any('options', 'AdminController@options');
        Route::any('resource', 'AdminController@resource');

        Route::get('status', 'AdminController@status');

        Route::prefix('users')->group(function () {
            Route::view('', 'admin.users');
            Route::post('', 'AdminController@userAjaxHandler');
            Route::get('list', 'AdminController@getUserData');
        });

        Route::prefix('players')->group(function () {
            Route::view('', 'admin.players');
            Route::post('', 'AdminController@playerAjaxHandler');
            Route::get('list', 'AdminController@getPlayerData');
        });

        Route::prefix('closet')->group(function () {
            Route::post('{uid}', 'ClosetManagementController@add');
            Route::delete('{uid}', 'ClosetManagementController@remove');
        });

        Route::prefix('reports')->group(function () {
            Route::view('', 'admin.reports');
            Route::post('', 'ReportController@review');
            Route::any('list', 'ReportController@manage');
        });

        Route::prefix('i18n')->group(function () {
            Route::view('', 'admin.i18n');
            Route::get('list', 'TranslationsController@list');
            Route::post('', 'TranslationsController@create');
            Route::put('', 'TranslationsController@update');
            Route::delete('', 'TranslationsController@delete');
        });

        Route::prefix('plugins')->group(function () {
            Route::get('data', 'PluginController@getPluginData');

            Route::view('manage', 'admin.plugins');
            Route::post('manage', 'PluginController@manage');
            Route::any('config/{name}', 'PluginController@config');
            Route::get('readme/{name}', 'PluginController@readme');
            Route::post('upload', 'PluginController@upload');
            Route::post('wget', 'PluginController@wget');

            Route::prefix('market')->group(function () {
                Route::view('', 'admin.market');
                Route::get('list', 'MarketController@marketData');
                Route::post('download', 'MarketController@download');
            });
        });

        Route::prefix('update')->middleware('role:super-admin')->group(function () {
            Route::get('', 'UpdateController@showUpdatePage');
            Route::post('download', 'UpdateController@download');
        });
    });

Route::prefix('setup')->group(function () {
    Route::middleware('setup')->group(function () {
        Route::view('', 'setup.wizard.welcome');
        Route::any('database', 'SetupController@database');
        Route::view('info', 'setup.wizard.info');
        Route::post('finish', 'SetupController@finish');
    });

    Route::middleware('authorize')->group(function () {
        Route::view('update', 'setup.updates.welcome')->middleware('setup');
        Route::any('exec-update', 'UpdateController@update')->middleware('setup');
    });
});

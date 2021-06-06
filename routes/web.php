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
        Route::post('login', 'AuthController@handleLogin');

        Route::get('register', 'AuthController@register')->name('register');
        Route::post('register', 'AuthController@handleRegister');

        Route::get('forgot', 'AuthController@forgot')->name('forgot');
        Route::post('forgot', 'AuthController@handleForgot');

        Route::get('reset/{uid}', 'AuthController@reset')->name('reset');
        Route::post('reset/{uid}', 'AuthController@handleReset');
    });

    Route::post('logout', 'AuthController@logout')->name('logout')->middleware('authorize');
    Route::any('captcha', 'AuthController@captcha');

    Route::middleware(['authorize', Middleware\EnsureEmailFilled::class])
        ->group(function () {
            Route::view('bind', 'auth.bind');
            Route::post('bind', 'AuthController@fillEmail');
        });

    Route::get('verify/{user}', 'AuthController@verify')->name('verify');
    Route::post('verify/{user}', 'AuthController@handleVerify');
});

Route::prefix('user')
    ->name('user.')
    ->middleware(['authorize'])
    ->group(function () {
        Route::get('', 'UserController@index')->name('home');
        Route::post('notifications/{id}', 'NotificationsController@read')->name('notification');
        Route::get('score-info', 'UserController@scoreInfo')->name('score');
        Route::post('sign', 'UserController@sign')->name('sign');

        Route::get('reports', 'ReportController@track');

        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('', 'UserController@profile');
            Route::post('', 'UserController@handleProfile');
            Route::post('avatar', 'UserController@setAvatar')->name('avatar');
        });

        Route::post('email-verification', 'UserController@sendVerificationEmail');

        Route::put('dark-mode', 'UserController@toggleDarkMode');

        Route::prefix('player')
            ->name('player.')
            ->middleware('verified')
            ->group(function () {
                Route::get('', 'PlayerController@index')->name('page');
                Route::get('list', 'PlayerController@list')->name('list');
                Route::post('', 'PlayerController@add')->name('add');
                Route::put('{player}/textures', 'PlayerController@setTexture')->name('set');
                Route::delete('{player}/textures', 'PlayerController@clearTexture')->name('clear');
                Route::put('{player}/name', 'PlayerController@rename')->name('rename');
                Route::delete('{player}', 'PlayerController@delete')->name('delete');
            });

        Route::prefix('closet')->name('closet.')->group(function () {
            Route::get('', 'ClosetController@index')->name('page');
            Route::get('list', 'ClosetController@getClosetData')->name('list');
            Route::get('ids', 'ClosetController@allIds')->name('ids');
            Route::post('', 'ClosetController@add')->name('add');
            Route::put('{tid}', 'ClosetController@rename')->name('rename');
            Route::delete('{tid}', 'ClosetController@remove')->name('remove');
        });

        // OAuth2 Management
        Route::view('oauth/manage', 'user.oauth')->middleware('verified');
    });

Route::prefix('texture')->name('texture.')->group(function () {
    Route::get('{texture}', 'SkinlibController@info')->name('info');
    Route::middleware(['authorize', 'verified'])->group(function () {
        Route::post('', 'SkinlibController@handleUpload')->name('upload');
        Route::prefix('{texture}')->group(function () {
            Route::put('type', 'SkinlibController@type')->name('type');
            Route::put('name', 'SkinlibController@rename')->name('name');
            Route::put('privacy', 'SkinlibController@privacy')->name('privacy');
            Route::delete('', 'SkinlibController@delete')->name('delete');
        });
    });
});

Route::prefix('skinlib')->name('skinlib.')->group(function () {
    Route::view('', 'skinlib.index')->name('home');
    Route::get('info/{texture}', 'SkinlibController@info')->name('info');
    Route::get('show/{texture}', 'SkinlibController@show')->name('show');
    Route::get('list', 'SkinlibController@library')->name('list');

    Route::middleware(['authorize', 'verified'])->group(function () {
        Route::get('upload', 'SkinlibController@upload');
        Route::post('report', 'ReportController@submit');
    });
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['authorize', 'role:admin'])
    ->group(function () {
        Route::get('', 'AdminController@index');
        Route::get('chart', 'AdminController@chartData');
        Route::post('notifications/send', 'NotificationsController@send');

        Route::any('customize', 'OptionsController@customize');
        Route::any('score', 'OptionsController@score');
        Route::any('options', 'OptionsController@options');
        Route::any('resource', 'OptionsController@resource');

        Route::get('status', 'AdminController@status');

        Route::prefix('users')->name('users.')->group(function () {
            Route::view('', 'admin.users');
            Route::get('list', 'UsersManagementController@list')->name('list');
            Route::prefix('{user}')->group(function () {
                Route::put('email', 'UsersManagementController@email')->name('email');
                Route::put('verification', 'UsersManagementController@verification')->name('verification');
                Route::put('nickname', 'UsersManagementController@nickname')->name('nickname');
                Route::put('password', 'UsersManagementController@password')->name('password');
                Route::put('score', 'UsersManagementController@score')->name('score');
                Route::put('permission', 'UsersManagementController@permission')->name('permission');
                Route::delete('', 'UsersManagementController@delete')->name('delete');
            });
        });

        Route::prefix('players')->name('players.')->group(function () {
            Route::view('', 'admin.players');
            Route::get('list', 'PlayersManagementController@list')->name('list');
            Route::prefix('{player}')->group(function () {
                Route::put('name', 'PlayersManagementController@name')->name('name');
                Route::put('owner', 'PlayersManagementController@owner')->name('owner');
                Route::put('textures', 'PlayersManagementController@texture')->name('texture');
                Route::delete('', 'PlayersManagementController@delete')->name('delete');
            });
        });

        Route::prefix('closet')->group(function () {
            Route::post('{user}', 'ClosetManagementController@add');
            Route::delete('{user}', 'ClosetManagementController@remove');
        });

        Route::prefix('reports')->group(function () {
            Route::view('', 'admin.reports');
            Route::put('{report}', 'ReportController@review');
            Route::get('list', 'ReportController@manage');
        });

        Route::prefix('i18n')->group(function () {
            Route::view('', 'admin.i18n');
            Route::get('list', 'TranslationsController@list');
            Route::post('', 'TranslationsController@create');
            Route::put('{line}', 'TranslationsController@update');
            Route::delete('{line}', 'TranslationsController@delete');
        });

        Route::prefix('plugins')->group(function () {
            Route::get('data', 'PluginController@getPluginData');

            Route::view('manage', 'admin.plugins');
            Route::post('manage', 'PluginController@manage');
            Route::any('config/{name}', 'PluginController@config');
            Route::get('readme/{name}', 'PluginController@readme');
            Route::middleware('role:super-admin')->group(function () {
                Route::post('upload', 'PluginController@upload');
                Route::post('wget', 'PluginController@wget');
            });

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
});

Route::prefix('.well-known')->group(function () {
    Route::redirect('change-password', '/user/profile');
});

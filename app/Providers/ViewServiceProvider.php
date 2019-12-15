<?php

namespace App\Providers;

use App\Http\View\Composers;
use App\Services\Webpack;
use Illuminate\Support\ServiceProvider;
use View;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(Webpack $webpack)
    {
        View::composer([
            'home',
            '*.base',
            '*.master',
            'shared.header',
        ], function ($view) {
            $lightColors = ['light', 'warning', 'white', 'orange'];
            $color = option('navbar_color');
            $view->with([
                'site_name' => option_localized('site_name'),
                'navbar_color' => $color,
                'color_mode' => in_array($color, $lightColors) ? 'light' : 'dark',
            ]);
        });

        View::composer('shared.head', Composers\HeadComposer::class);

        View::composer('shared.notifications', function ($view) {
            $notifications = auth()->user()->unreadNotifications;
            $view->with([
                'notifications' => $notifications,
                'amount' => count($notifications),
            ]);
        });

        View::composer('shared.languages', Composers\LanguagesMenuComposer::class);

        View::composer('shared.user-menu', Composers\UserMenuComposer::class);

        View::composer('shared.sidebar', function ($view) {
            $view->with('sidebar_color', option('sidebar_color'));
        });

        View::composer('shared.side-menu', Composers\SideMenuComposer::class);

        View::composer('shared.user-panel', Composers\UserPanelComposer::class);

        View::composer('shared.copyright', function ($view) {
            $customCopyright = get_string_replaced(
                option_localized('copyright_text'),
                [
                    '{site_name}' => option_localized('site_name'),
                    '{site_url}' => option('site_url'),
                ]
            );
            $view->with([
                'copyright' => option_localized('copyright_prefer', 0),
                'custom_copyright' => $customCopyright,
            ]);
        });

        View::composer('shared.foot', Composers\FootComposer::class);

        View::composer('auth.*', function ($view) {
            $view->with('enable_recaptcha', (bool) option('recaptcha_sitekey'));
            $view->with(
                'recaptcha_url',
                'https://www.recaptcha.net/recaptcha/api.js'
                .'?onload=vueRecaptchaApiLoaded&render=explicit'
            );
        });

        View::composer(['errors.*', 'setup.*'], function ($view) use ($webpack) {
            $view->with([
                'styles' => [
                    $webpack->url('setup.css'),
                ],
                'scripts' => [
                    $webpack->url('language-chooser.js'),
                ],
            ]);
        });

        View::composer('auth.oauth', function ($view) {
            $view->with('providers', resolve('oauth.providers'));
        });
    }
}

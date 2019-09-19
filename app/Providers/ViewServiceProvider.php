<?php

namespace App\Providers;

use View;
use App\Services\Webpack;
use App\Http\View\Composers;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(Webpack $webpack)
    {
        View::composer(['home', '*.base', 'shared.header'], function ($view) {
            $view->with([
                'site_name' => option_localized('site_name'),
                'color_scheme' => option('color_scheme'),
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
    }
}

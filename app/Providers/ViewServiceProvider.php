<?php

namespace App\Providers;

use View;
use App\Http\View\Composers;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot()
    {
        View::composer(['*.base', 'shared.header'], function ($view) {
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

        View::composer('shared.footer', function ($view) {
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
    }
}

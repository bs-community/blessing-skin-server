<?php

declare(strict_types=1);

namespace App\Services;

use App\Events;
use App\Notifications;
use Blessing\Filter;
use Closure;
use Event;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Notification;

class Hook
{
    /**
     * Add an item to menu.
     *
     * @param string $category 'user' or 'admin' or 'explore'
     * @param int    $position where to insert the given item, start from 0
     * @param array  $menu     e.g.
     *                         [
     *                         'title' => 'Title',       # will be translated by translator
     *                         'link'  => 'user/config', # route link
     *                         'icon'  => 'fa-book',     # font-awesome icon
     *                         'new-tab' => false,        # open the link in new tab or not, false by default
     *                         ]
     */
    public static function addMenuItem(string $category, int $position, array $menu): void
    {
        $class = 'App\Events\Configure'.Str::title($category).'Menu';

        Event::listen($class, function ($event) use ($menu, $position, $category) {
            $new = [];

            $offset = 0;
            foreach ($event->menu[$category] as $item) {
                // Push new menu items at the given position
                if ($offset == $position) {
                    $new[] = $menu;
                }

                $new[] = $item;
                $offset++;
            }

            if ($position >= $offset) {
                $new[] = $menu;
            }

            $event->menu[$category] = $new;
        });
    }

    public static function addRoute(Closure $callback): void
    {
        Event::listen(Events\ConfigureRoutes::class, function ($event) use ($callback) {
            return call_user_func($callback, $event->router);
        });
    }

    public static function addStyleFileToPage($urls, $pages = ['*']): void
    {
        $urls = collect($urls);
        $pages = collect($pages);
        resolve(Filter::class)->add('head_links', function ($links) use ($urls, $pages) {
            $matched = $pages->some(fn ($page) => request()->is($page));
            if ($matched) {
                $urls->each(function ($url) use (&$links) {
                    $links[] = [
                        'rel' => 'stylesheet',
                        'href' => $url,
                        'crossorigin' => 'anonymous',
                    ];
                });
            }

            return $links;
        });
    }

    public static function addScriptFileToPage($urls, $pages = ['*']): void
    {
        $urls = collect($urls);
        $pages = collect($pages);
        resolve(Filter::class)->add('scripts', function ($scripts) use ($urls, $pages) {
            $matched = $pages->some(fn ($page) => request()->is($page));
            if ($matched) {
                $urls->each(function ($url) use (&$scripts) {
                    $scripts[] = ['src' => $url, 'crossorigin' => 'anonymous'];
                });
            }

            return $scripts;
        });
    }

    /** @deprecated */
    public static function addUserBadge(string $text, $color = 'primary'): void
    {
        resolve(Filter::class)->add('user_badges', function ($badges) use ($text, $color) {
            $badges[] = ['text' => $text, 'color' => $color];

            return $badges;
        });
    }

    public static function sendNotification($users, string $title, $content = ''): void
    {
        Notification::send(Arr::wrap($users), new Notifications\SiteMessage($title, $content));
    }

    public static function pushMiddleware($middleware)
    {
        app()->make('Illuminate\Contracts\Http\Kernel')->pushMiddleware($middleware);
    }
}

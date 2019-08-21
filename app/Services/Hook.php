<?php

declare(strict_types=1);

namespace App\Services;

use Event;
use Closure;
use App\Events;
use Notification;
use App\Notifications;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Hook
{
    /**
     * Add an item to menu.
     *
     * @param string  $category  'user' or 'admin' or 'explore'
     * @param int  $position  Where to insert the given item, start from 0.
     * @param array  $menu  e.g.
     * [
     *     'title' => 'Title',       # will be translated by translator
     *     'link'  => 'user/config', # route link
     *     'icon'  => 'fa-book',     # font-awesome icon
     *     'new-tab' => false,        # open the link in new tab or not, false by default
     * ]
     * @return void
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

    /**
     * Add routes. A router instance will be passed to the given callback.
     */
    public static function addRoute(Closure $callback): void
    {
        Event::listen(Events\ConfigureRoutes::class, function ($event) use ($callback) {
            return call_user_func($callback, $event->router);
        });
    }

    public static function registerPluginTransScripts(string $name, $pages = ['*'], $priority = 999): void
    {
        Event::listen(Events\RenderingFooter::class, function ($event) use ($name, $pages) {
            foreach ($pages as $pattern) {
                if (! request()->is($pattern)) {
                    continue;
                }

                // We will determine current locale in the event callback,
                // otherwise the locale is not properly detected.
                $basepath = config('plugins.url') ?: url('plugins').'/'.$name.'/';
                $relative = 'lang/'.config('app.locale').'/locale.js';

                $event->addContent(
                    '<script src="'.$basepath.$relative.'"></script>'
                );

                return;
            }
        }, $priority);
    }

    public static function addStyleFileToPage($urls, $pages = ['*'], $priority = 1): void
    {
        Event::listen(Events\RenderingHeader::class, function ($event) use ($urls, $pages) {
            foreach ($pages as $pattern) {
                if (! request()->is($pattern)) {
                    continue;
                }

                foreach ((array) $urls as $url) {
                    $event->addContent("<link rel=\"stylesheet\" href=\"$url\">");
                }

                return;
            }
        }, $priority);
    }

    public static function addScriptFileToPage($urls, $pages = ['*'], $priority = 1): void
    {
        Event::listen(Events\RenderingFooter::class, function ($event) use ($urls, $pages) {
            foreach ($pages as $pattern) {
                if (! request()->is($pattern)) {
                    continue;
                }

                foreach ((array) $urls as $url) {
                    $event->addContent("<script src=\"$url\"></script>");
                }

                return;
            }
        }, $priority);
    }

    public static function addUserBadge(string $text, $color = 'primary'): void
    {
        Event::listen(
            Events\RenderingBadges::class,
            function (Events\RenderingBadges $event) use ($text, $color) {
                $event->badges[] = [$text, $color];
            }
        );
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

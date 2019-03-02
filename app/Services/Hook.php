<?php

namespace App\Services;

use Event;
use Closure;
use App\Events;

class Hook
{
    /**
     * Add an item to menu.
     *
     * @param string  $category  'user' or 'admin'
     * @param int  $position  Where to insert the given item, start from 0.
     * @param array  $menu  e.g.
     * [
     *     'title' => 'Title',       # will be translated by translator
     *     'link'  => 'user/config', # route link
     *     'icon'  => 'fa-book'      # font-awesome icon
     * ]
     * @return void
     */
    public static function addMenuItem($category, $position, array $menu)
    {
        $class = $category == 'user' ? Events\ConfigureUserMenu::class : Events\ConfigureAdminMenu::class;

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
     * Add a route. A router instance will be passed to the given callback.
     *
     * @param Closure $callback
     *
     * TODO: Needs to be tested.
     */
    public static function addRoute(Closure $callback)
    {
        Event::listen(Events\ConfigureRoutes::class, function ($event) use ($callback) {
            return call_user_func($callback, $event->router);
        });
    }

    public static function registerPluginTransScripts($id, $pages = ['*'], $priority = 999)
    {
        Event::listen(Events\RenderingFooter::class, function ($event) use ($id, $pages) {
            foreach ($pages as $pattern) {
                if (! app('request')->is($pattern)) {
                    continue;
                }

                // We will determine current locale in the event callback,
                // otherwise the locale is not properly detected.
                $basepath = plugin($id)->getPath().'/';
                $relative = 'lang/'.config('app.locale').'/locale.js';

                if (file_exists($basepath.$relative)) {
                    $event->addContent('<script src="'.plugin_assets($id, $relative).'"></script>');
                }

                return;
            }
        }, $priority);
    }

    public static function addStyleFileToPage($urls, $pages = ['*'], $priority = 1)
    {
        Event::listen(Events\RenderingHeader::class, function ($event) use ($urls, $pages) {
            foreach ($pages as $pattern) {
                if (! app('request')->is($pattern)) {
                    continue;
                }

                foreach ((array) $urls as $url) {
                    $event->addContent("<link rel=\"stylesheet\" href=\"$url\">");
                }

                return;
            }
        }, $priority);
    }

    public static function addScriptFileToPage($urls, $pages = ['*'], $priority = 1)
    {
        Event::listen(Events\RenderingFooter::class, function ($event) use ($urls, $pages) {
            foreach ($pages as $pattern) {
                if (! app('request')->is($pattern)) {
                    continue;
                }

                foreach ((array) $urls as $url) {
                    $event->addContent("<script src=\"$url\"></script>");
                }

                return;
            }
        }, $priority);
    }
}

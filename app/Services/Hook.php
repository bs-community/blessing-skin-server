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
        $class = $category == "user" ? Events\ConfigureUserMenu::class : Events\ConfigureAdminMenu::class;

        Event::listen($class, function ($event) use ($menu, $position, $category)
        {
            $new = [];

            $offset = 0;
            foreach ($event->menu[$category] as $item) {
                // push new menu items at the given position
                if ($offset == $position) {
                    $new[] = $menu;
                }

                $new[] = $item;
                $offset++;
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
        Event::listen(Events\ConfigureRoutes::class, function($event) use ($callback)
        {
            return call_user_func($callback, $event->router);
        });
    }

    public static function registerPluginTransScripts($id)
    {
        Event::listen(Events\RenderingFooter::class, function($event) use ($id)
        {
            $path   = app('plugins')->getPlugin($id)->getPath().'/';
            $script = 'lang/'.config('app.locale').'/locale.js';

            if (file_exists($path.$script)) {
                $event->addContent('<script src="'.plugin_assets($id, $script).'"></script>');
            }
        }, 999);
    }

    public static function addStyleFileToPage($urls, $pages = ['*'], $priority = 1)
    {
        Event::listen(Events\RenderingHeader::class, function($event) use ($urls, $pages)
        {
            foreach ($pages as $pattern) {
                if (!app('request')->is($pattern))
                    continue;

                foreach ((array) $urls as $url) {
                    $event->addContent("<link rel=\"stylesheet\" href=\"$url\">");
                }

                return;
            }

        }, $priority);
    }

    public static function addScriptFileToPage($urls, $pages = ['*'], $priority = 1)
    {
        Event::listen(Events\RenderingFooter::class, function($event) use ($urls, $pages)
        {
            foreach ($pages as $pattern) {
                if (!app('request')->is($pattern))
                    continue;

                foreach ((array) $urls as $url) {
                    $event->addContent("<script src=\"$url\"></script>");
                }

                return;
            }

        }, $priority);
    }
}

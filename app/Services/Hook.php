<?php

namespace App\Services;

use Event;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Events\ConfigureRoutes;
use App\Events\ConfigureUserMenu;
use App\Events\ConfigureAdminMenu;

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
        $class = $category == "user" ? ConfigureUserMenu::class : ConfigureAdminMenu::class;

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
     */
    public static function addRoute(Closure $callback)
    {
        Event::listen(ConfigureRoutes::class, function($event) use ($callback)
        {
            return call_user_func($callback, $event->router);
        });
    }
}

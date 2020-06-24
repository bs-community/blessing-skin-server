<?php

namespace App\Events;

class ConfigureUserMenu extends Event
{
    public $menu;

    public function __construct(array &$menu)
    {
        $this->menu = &$menu;
    }
}

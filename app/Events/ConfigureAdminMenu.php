<?php

namespace App\Events;

class ConfigureAdminMenu extends Event
{
    public $menu;

    public function __construct(array &$menu)
    {
        // Pass array by reference
        $this->menu = &$menu;
    }
}

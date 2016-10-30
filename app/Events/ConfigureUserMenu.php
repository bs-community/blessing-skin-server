<?php

namespace App\Events;

class ConfigureUserMenu extends Event
{
    public $menu;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(array &$menu)
    {
        // pass array by reference
        $this->menu = &$menu;
    }
}

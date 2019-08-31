<?php

namespace App\Events;

use App\Services\Plugin;

class PluginBootFailed extends Event
{
    /** @var Plugin */
    public $plugin;

    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }
}

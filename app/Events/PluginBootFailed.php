<?php

namespace App\Events;

use App\Services\Plugin;

class PluginBootFailed extends Event
{
    public Plugin $plugin;

    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }
}

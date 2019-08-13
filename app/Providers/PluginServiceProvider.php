<?php

namespace App\Providers;

use Event;
use App\Events;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Services\PluginManager;
use Illuminate\Support\ServiceProvider;

class PluginServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('plugins', PluginManager::class);
    }

    public function boot(PluginManager $plugins)
    {
        $plugins->boot();
    }
}

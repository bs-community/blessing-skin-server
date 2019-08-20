<?php

namespace App\Providers;

use App\Services\PluginManager;
use Illuminate\Support\ServiceProvider;

class PluginServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(PluginManager::class);
        $this->app->singleton('plugins', function ($app) {
            return $app->make(PluginManager::class);
        });
    }

    public function boot(PluginManager $plugins)
    {
        $plugins->boot();
    }
}

<?php

namespace App\Providers;

use App\Services\PluginManager;
use Illuminate\Support\ServiceProvider;

class PluginServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PluginManager::class);
        $this->app->alias(PluginManager::class, 'plugins');
    }

    public function boot(PluginManager $plugins): void
    {
        $plugins->boot();
    }
}

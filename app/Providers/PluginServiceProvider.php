<?php

namespace App\Providers;

use Illuminate\Support\Str;
use App\Services\PluginManager;
use Illuminate\Support\ServiceProvider;

class PluginServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(PluginManager $plugins)
    {
        $namespaces = [];

        foreach ($plugins->getPlugins() as $plugin) {
            $namespaces[$plugin->getNameSpace()] = $plugin->getPath()."/src";
        }

        // register class autoloader for plugins
        spl_autoload_register(function($class) use ($namespaces) {
            // traverse in registered plugin namespaces
            foreach ((array) array_keys($namespaces) as $namespace) {
                if ($namespace != '' && mb_strpos($class, $namespace) === 0) {
                    // parse real file path
                    $path = $namespaces[$namespace].Str::replaceFirst($namespace, '', $class).".php";
                    $path = str_replace('\\', '/', $path);

                    if (file_exists($path)) {
                        include $path;
                    }
                }
            }
        });

        $bootstrappers = $plugins->getEnabledBootstrappers();

        foreach ($bootstrappers as $file) {
            // bootstraper is a closure
            $bootstrapper = require $file;

            $this->app->call($bootstrapper);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('plugins', PluginManager::class);
    }
}

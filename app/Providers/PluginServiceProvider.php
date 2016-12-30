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
        // store paths of class files of plugins
        $src_paths = [];

        $loader = $this->app->make('translation.loader');
        // make view instead of view.finder since the finder is defined as not a singleton
        $finder = $this->app->make('view');

        foreach ($plugins->getEnabledPlugins() as $plugin) {
            $src_paths[$plugin->getNameSpace()] = $plugin->getPath()."/src";
            // add paths of translation files for namespace hints
            $loader->addNamespace($plugin->getNameSpace(), $plugin->getPath()."/lang");
            // add paths of views
            $finder->addNamespace($plugin->getNameSpace(), $plugin->getPath()."/views");
        }

        $this->registerClassAutoloader($src_paths);

        $bootstrappers = $plugins->getEnabledBootstrappers();

        foreach ($bootstrappers as $file) {
            $bootstrapper = require $file;
            // call closure using service container
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

    /**
     * Register class autoloader for plugins.
     *
     * @return void
     */
    protected function registerClassAutoloader($paths)
    {
        spl_autoload_register(function ($class) use ($paths) {
            // traverse in registered plugin paths
            foreach ((array) array_keys($paths) as $namespace) {
                if ($namespace != '' && mb_strpos($class, $namespace) === 0) {
                    // parse real file path
                    $path = $paths[$namespace].Str::replaceFirst($namespace, '', $class).".php";
                    $path = str_replace('\\', '/', $path);

                    if (file_exists($path)) {
                        // include class file if it exists
                        include $path;
                    }
                }
            }
        });
    }
}

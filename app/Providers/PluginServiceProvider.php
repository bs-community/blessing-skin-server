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
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(PluginManager $plugins)
    {
        // Disable plugins which has unsatisfied dependencies
        $this->disableRequirementsUnsatisfiedPlugins($plugins);

        // Store paths of class files of plugins
        $src_paths = [];

        $loader = $this->app->make('translation.loader');
        // Make view instead of view.finder since the finder is defined as not a singleton
        $finder = $this->app->make('view');

        foreach ($plugins->getPlugins() as $plugin) {
            if ($plugin->isEnabled()) {
                $src_paths[$plugin->getNameSpace()] = $plugin->getPath().'/src';
                // Add paths of views
                $finder->addNamespace($plugin->getNameSpace(), $plugin->getPath().'/views');
            }

            // Always add paths of translation files for namespace hints
            $loader->addNamespace($plugin->getNameSpace(), $plugin->getPath().'/lang');
        }

        $this->registerPluginCallbackListener();
        $this->registerClassAutoloader($src_paths);

        // Register plugin's own composer autoloader
        foreach ($plugins->getEnabledComposerAutoloaders() as $autoloader) {
            require $autoloader;
        }

        $bootstrappers = $plugins->getEnabledBootstrappers();

        foreach ($bootstrappers as $file) {
            $bootstrapper = require $file;
            // Call closure using service container
            $this->app->call($bootstrapper);
        }
    }

    protected function disableRequirementsUnsatisfiedPlugins(PluginManager $manager)
    {
        foreach ($manager->getEnabledPlugins() as $plugin) {
            if (! $manager->isRequirementsSatisfied($plugin)) {
                $manager->disable($plugin->name);
            }
        }
    }

    protected function registerPluginCallbackListener()
    {
        Event::listen([
            Events\PluginWasEnabled::class,
            Events\PluginWasDeleted::class,
            Events\PluginWasDisabled::class,
        ], function ($event) {
            // Call callback functions of plugin
            if (file_exists($filename = $event->plugin->getPath().'/callbacks.php')) {
                $callbacks = require $filename;

                $callback = Arr::get($callbacks, get_class($event));

                return $callback ? app()->call($callback, [$event->plugin]) : null;
            }
        });
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
            // Traverse in registered plugin paths
            foreach ((array) array_keys($paths) as $namespace) {
                if ($namespace != '' && mb_strpos($class, $namespace) === 0) {
                    // Parse real file path
                    $path = $paths[$namespace].Str::replaceFirst($namespace, '', $class).'.php';
                    $path = str_replace('\\', '/', $path);

                    if (file_exists($path)) {
                        // Include class file if it exists
                        include $path;
                    }
                }
            }
        });
    }
}

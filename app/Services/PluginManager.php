<?php

namespace App\Services;

use App\Events;
use Composer\Semver\Semver;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Composer\Semver\Comparator;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use App\Exceptions\PrettyPageException;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;

class PluginManager
{
    /**
     * @var bool
     */
    protected $booted = false;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Option
     */
    protected $option;

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Collection|null
     */
    protected $plugins;

    /**
     * @var Collection
     */
    protected $enabled;

    public function __construct(
        Application $app,
        Option $option,
        Dispatcher $dispatcher,
        Filesystem $filesystem
    ) {
        $this->app = $app;
        $this->option = $option;
        $this->dispatcher = $dispatcher;
        $this->filesystem = $filesystem;
        $this->enabled = collect();
    }

    /**
     * Get all installed plugins.
     *
     * @return Collection
     */
    public function all()
    {
        if (filled($this->plugins)) {
            return $this->plugins;
        }

        $this->enabled = collect(json_decode($this->option->get('plugins_enabled', '[]'), true))
            ->mapWithKeys(function ($item) {
                return [$item['name'] => ['version' => $item['version']]];
            });
        $plugins = collect();

        collect($this->filesystem->directories($this->getPluginsDir()))
            ->filter(function ($directory) {
                return $this->filesystem->exists($directory.DIRECTORY_SEPARATOR.'package.json');
            })
            ->each(function ($directory) use (&$plugins) {
                $manifest = json_decode(
                    $this->filesystem->get($directory.DIRECTORY_SEPARATOR.'package.json'),
                    true
                );

                $name = $manifest['name'];
                if ($plugins->has($name)) {
                    throw new PrettyPageException(trans('errors.plugins.duplicate', [
                        'dir1' => $plugins->get($name)->getPath(),
                        'dir2' => $directory,
                    ]), 5);
                }

                $plugin = new Plugin($directory, $manifest);
                $plugins->put($name, $plugin);
                if ($this->getUnsatisfied($plugin)->isNotEmpty()) {
                    $this->disable($plugin);
                }
                if ($this->enabled->has($name)) {
                    $plugin->setEnabled(true);
                    if (Comparator::notEqualTo(
                        $manifest['version'],
                        $this->enabled->get($name)['version']
                    )) {
                        $this->enabled->put($name, $manifest['version']);
                        $this->dispatcher->dispatch(new Events\PluginVersionChanged($plugin));
                    }
                }
            });

        $this->plugins = $plugins;

        return $plugins;
    }

    /**
     * Boot all enabled plugins.
     */
    public function boot()
    {
        if ($this->booted) {
            return;
        }

        $enabled = $this->getEnabledPlugins();

        $this->registerAutoload(
            $enabled->mapWithKeys(function ($plugin) {
                return [$plugin->namespace => $plugin->getPath().'/src'];
            })
        );
        $this->loadVendor($enabled);
        $this->loadViewsAndTranslations($enabled);
        $this->loadBootstrapper($enabled);
        $this->registerLifecycleHooks();

        $this->booted = true;
    }

    /**
     * Register classes autoloading.
     *
     * @param Collection $paths
     */
    protected function registerAutoload($paths)
    {
        spl_autoload_register(function ($class) use ($paths) {
            $paths->each(function ($path, $namespace) use ($class) {
                if ($namespace != '' && mb_strpos($class, $namespace) === 0) {
                    // Parse real file path
                    $path = $path.Str::replaceFirst($namespace, '', $class).'.php';
                    $path = str_replace('\\', '/', $path);

                    if ($this->filesystem->exists($path)) {
                        $this->filesystem->getRequire($path);
                    }
                }
            });
        });
    }

    /**
     * Load Composer dumped autoload file.
     *
     * @param Collection $enabled
     */
    protected function loadVendor($enabled)
    {
        $enabled->each(function ($plugin) {
            $path = $plugin->getPath().'/vendor/autoload.php';
            if ($this->filesystem->exists($path)) {
                $this->filesystem->getRequire($path);
            }
        });
    }

    /**
     * Load views and translations.
     *
     * @param Collection $enabled
     */
    protected function loadViewsAndTranslations($enabled)
    {
        $translations = $this->app->make('translation.loader');
        $view = $this->app->make('view');
        $enabled->each(function ($plugin) use (&$translations, &$view) {
            $namespace = $plugin->namespace;
            $path = $plugin->getPath();

            $translations->addNamespace($namespace, $path.'/lang');
            $view->addNamespace($namespace, $path.'/views');
        });
    }

    /**
     * Load plugin's bootstrapper.
     *
     * @param Collection $enabled
     */
    protected function loadBootstrapper($enabled)
    {
        $enabled->each(function ($plugin) {
            $path = $plugin->getPath().'/bootstrap.php';
            if ($this->filesystem->exists($path)) {
                $this->app->call($this->filesystem->getRequire($path));
            }
        });
    }

    protected function registerLifecycleHooks()
    {
        $this->dispatcher->listen([
            Events\PluginWasEnabled::class,
            Events\PluginWasDisabled::class,
            Events\PluginWasDeleted::class,
        ], function ($event) {
            $plugin = $event->plugin;
            $path = $plugin->getPath().'/callbacks.php';
            if ($this->filesystem->exists($path)) {
                $callbacks = $this->filesystem->getRequire($path);
                $callback = Arr::get($callbacks, get_class($event));
                if ($callback) {
                    return $this->app->call($callback, [$plugin]);
                }
            }
        });
    }

    /**
     * @return Plugin|null
     */
    public function get(string $name)
    {
        return $this->all()->get($name);
    }

    public function enable($plugin)
    {
        $plugin = is_string($plugin) ? $this->get($plugin) : $plugin;
        if ($plugin && ! $plugin->isEnabled()) {
            $this->enabled->put($plugin->name, ['version' => $plugin->version]);
            $this->saveEnabled();

            $plugin->setEnabled(true);

            $this->dispatcher->dispatch(new Events\PluginWasEnabled($plugin));
        }
    }

    public function disable($plugin)
    {
        $plugin = is_string($plugin) ? $this->get($plugin) : $plugin;
        if ($plugin && $plugin->isEnabled()) {
            $this->enabled->pull($plugin->name);
            $this->saveEnabled();

            $plugin->setEnabled(false);

            $this->dispatcher->dispatch(new Events\PluginWasDisabled($plugin));
        }
    }

    public function delete($plugin)
    {
        $plugin = is_string($plugin) ? $this->get($plugin) : $plugin;
        if ($plugin) {
            $this->disable($plugin);

            // dispatch event before deleting plugin files
            $this->dispatcher->dispatch(new Events\PluginWasDeleted($plugin));

            $this->filesystem->deleteDirectory($plugin->getPath());

            $this->plugins->pull($plugin->name);
        }
    }

    /**
     * @return Collection
     */
    public function getEnabledPlugins()
    {
        return $this->all()->filter(function ($plugin) {
            return $plugin->isEnabled();
        });
    }

    /**
     * Persist the currently enabled plugins.
     */
    protected function saveEnabled()
    {
        $this->option->set('plugins_enabled', $this->enabled->map(function ($info, $name) {
            return array_merge(compact('name'), $info);
        })->values()->toJson());
    }

    /**
     * @param Plugin $plugin
     * @return Collection
     */
    public function getUnsatisfied(Plugin $plugin)
    {
        return collect(Arr::get($plugin->getManifest(), 'require', []))
            ->mapWithKeys(function ($constraint, $name) {
                if ($name == 'blessing-skin-server') {
                    $version = config('app.version');

                    return (! Semver::satisfies($version, $constraint))
                        ? [$name => compact('version', 'constraint')]
                        : [];
                } elseif ($name == 'php') {
                    $version = PHP_VERSION;

                    return (! Semver::satisfies($version, $constraint))
                        ? [$name => compact('version', 'constraint')]
                        : [];
                } elseif (! $this->enabled->has($name)) {
                    return [$name => ['version' => null, 'constraint' => $constraint]];
                } else {
                    $version = $this->enabled->get($name)['version'];

                    return (! Semver::satisfies($version, $constraint))
                        ? [$name => compact('version', 'constraint')]
                        : [];
                }
            });
    }

    /**
     * The plugins path.
     *
     * @return string
     */
    public function getPluginsDir()
    {
        return config('plugins.directory') ? realpath(config('plugins.directory')) : base_path('plugins');
    }
}

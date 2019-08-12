<?php

namespace App\Services;

use Storage;
use Exception;
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
     * Boot all enabled plugins.
     */
    public function boot()
    {
        if ($this->booted) {
            return;
        }

        $this->enabled = collect(json_decode($this->option->get('plugins_enabled', '[]'), true));
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
                if ($this->enabled->contains('name', $name)) {
                    $plugin->setEnabled(true);
                    if (Comparator::notEqualTo(
                        $manifest['version'],
                        $this->enabled->firstWhere('name', $name)['version']
                    )) {
                        $this->dispatcher->dispatch(new Events\PluginVersionChanged($plugin));
                    }
                }
            });

        $enabled = $plugins->filter(function ($plugin) {
            return $plugin->isEnabled();
        });

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
     * @return Collection
     */
    public function getPlugins()
    {
        if (is_null($this->plugins)) {
            $plugins = new Collection();
            $enabled = $this->getFullEnabled();

            $installed = [];

            $cwd = getcwd();
            chdir(base_path());

            try {
                $resource = opendir($this->getPluginsDir());
            } catch (\Exception $e) {
                throw new PrettyPageException(trans('errors.plugins.directory', ['msg' => $e->getMessage()]), 500);
            }

            // traverse plugins dir
            while ($filename = @readdir($resource)) {
                if ($filename == '.' || $filename == '..') {
                    continue;
                }

                $path = $this->getPluginsDir().DIRECTORY_SEPARATOR.$filename;

                if (is_dir($path)) {
                    $packageJsonPath = $path.DIRECTORY_SEPARATOR.'package.json';

                    if (file_exists($packageJsonPath)) {
                        // load packages installed
                        $installed[$filename] = json_decode($this->filesystem->get($packageJsonPath), true);
                    }
                }
            }
            closedir($resource);

            foreach ($installed as $dirname => $package) {

                // Instantiates an Plugin object using the package path and package.json file.
                $plugin = new Plugin($this->getPluginsDir().DIRECTORY_SEPARATOR.$dirname, $package);

                // Each default all plugins are installed if they are registered in composer.
                $plugin->setDirname($dirname);
                $plugin->setInstalled(true);
                $plugin->setNameSpace(Arr::get($package, 'namespace'));
                $plugin->setVersion(Arr::get($package, 'version'));
                $plugin->setEnabled($this->isEnabled($plugin->name));

                if ($plugins->has($plugin->name)) {
                    throw new PrettyPageException(trans('errors.plugins.duplicate', [
                        'dir1' => $plugin->getDirname(),
                        'dir2' => $plugins->get($plugin->name)->getDirname(),
                    ]), 5);
                }

                $plugins->put($plugin->name, $plugin);

                if (
                    $enabled->has($plugin->name) &&
                    Comparator::notEqualTo($plugin->getVersion(), $enabled->get($plugin->name))
                ) {
                    $this->copyPluginAssets($plugin);
                }
            }

            $this->plugins = $plugins->sortBy(function ($plugin, $name) {
                return $plugin->name;
            });

            chdir($cwd);
        }

        return $this->plugins;
    }

    /**
     * Loads an Plugin with all information.
     *
     * @param string $name
     * @return Plugin|null
     */
    public function getPlugin($name)
    {
        return $this->getPlugins()->get($name);
    }

    /**
     * Enables the plugin.
     *
     * @param string $name
     */
    public function enable($name)
    {
        if (is_null($this->enabled)) {
            $this->convertPluginRecord();
        }

        if (! $this->isEnabled($name)) {
            $plugin = $this->getPlugin($name);

            $this->enabled->push([
                'name' => $name,
                'version' => $plugin->getVersion(),
            ]);
            $this->saveEnabled();

            $plugin->setEnabled(true);

            $this->copyPluginAssets($plugin);

            $this->dispatcher->dispatch(new Events\PluginWasEnabled($plugin));
        }
    }

    /**
     * Disables an plugin.
     *
     * @param string $name
     */
    public function disable($name)
    {
        if (is_null($this->enabled)) {
            $this->convertPluginRecord();
        }

        $rejected = $this->enabled->reject(function ($item) use ($name) {
            return is_string($item) ? $item == $name : $item['name'] == $name;
        });

        if ($rejected->count() !== $this->enabled->count()) {
            $plugin = $this->getPlugin($name);
            $plugin->setEnabled(false);

            $this->enabled = $rejected;
            $this->saveEnabled();

            $this->dispatcher->dispatch(new Events\PluginWasDisabled($plugin));
        }
    }

    /**
     * Uninstalls an plugin.
     *
     * @param string $name
     */
    public function uninstall($name)
    {
        $plugin = $this->getPlugin($name);

        $this->disable($name);

        // dispatch event before deleting plugin files
        $this->dispatcher->dispatch(new Events\PluginWasDeleted($plugin));

        $this->filesystem->deleteDirectory($plugin->getPath());

        // refresh plugin list
        $this->plugins = null;
    }

    /**
     * Get only enabled plugins.
     *
     * @return Collection
     */
    public function getEnabledPlugins()
    {
        if (is_null($this->enabled)) {
            $this->convertPluginRecord();
        }

        return $this->getPlugins()->only($this->getEnabled());
    }

    /**
     * Loads all bootstrap.php files of the enabled plugins.
     *
     * @return Collection
     */
    public function getEnabledBootstrappers()
    {
        $bootstrappers = new Collection;

        foreach ($this->getEnabledPlugins() as $plugin) {
            if ($this->filesystem->exists($file = $plugin->getPath().'/bootstrap.php')) {
                $bootstrappers->push($file);
            }
        }

        return $bootstrappers;
    }

    /**
     * Loads composer autoloader for the enabled plugins if exists.
     *
     * @return Collection
     */
    public function getEnabledComposerAutoloaders()
    {
        $autoloaders = new Collection;

        foreach ($this->getEnabledPlugins() as $plugin) {
            if ($this->filesystem->exists($file = $plugin->getPath().'/vendor/autoload.php')) {
                $autoloaders->push($file);
            }
        }

        return $autoloaders;
    }

    /**
     * The id's of the enabled plugins.
     *
     * @return array
     */
    public function getEnabled()
    {
        $enabled = collect(json_decode($this->option->get('plugins_enabled'), true));

        return $enabled->map(function ($item) {
            if (is_string($item)) {
                return $item;
            } else {
                return $item['name'];
            }
        })->values()->toArray();
    }

    /**
     * Return enabled plugins with version information.
     *
     * @return Collection
     */
    public function getFullEnabled()
    {
        $enabled = collect(json_decode($this->option->get('plugins_enabled'), true));
        $ret = collect();

        $enabled->each(function ($item) use ($ret) {
            if (is_array($item)) {
                $ret->put($item['name'], $item['version']);
            }
        });

        return $ret;
    }

    /**
     * Persist the currently enabled plugins.
     */
    protected function saveEnabled()
    {
        $this->option->set('plugins_enabled', $this->enabled->values()->toJson());
    }

    /**
     * Whether the plugin is enabled.
     *
     * @param  string $pluginName
     * @return bool
     */
    public function isEnabled($pluginName)
    {
        return in_array($pluginName, $this->getEnabled());
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
                } elseif (! $this->enabled->contains('name', $name)) {
                    return [$name => ['version' => null, 'constraint' => $constraint]];
                } else {
                    $version = $this->enabled->firstWhere('name', $name)['version'];
                    return (! Semver::satisfies($version, $constraint))
                        ? [$name => compact('version', 'constraint')]
                        : [];
                }
            });
    }

    /**
     * Get the unsatisfied requirements of plugin.
     *
     * @param  string|Plugin|array $plugin
     * @return array
     */
    public function getUnsatisfiedRequirements($plugin)
    {
        if (is_array($plugin)) {
            $requirements = $plugin;
        } else {
            if (! $plugin instanceof Plugin) {
                $plugin = $this->getPlugin($plugin);
            }

            if (! $plugin) {
                throw new \InvalidArgumentException('Plugin with given name does not exist.');
            }

            $requirements = $plugin->getRequirements();
        }

        $unsatisfied = [];

        foreach ($requirements as $name => $versionConstraint) {
            // Version requirement for the main application
            if ($name == 'blessing-skin-server') {
                if (! Semver::satisfies(config('app.version'), $versionConstraint)) {
                    $unsatisfied['blessing-skin-server'] = [
                        'version' => config('app.version'),
                        'constraint' => $versionConstraint,
                    ];
                }

                continue;
            }

            $requiredPlugin = $this->getPlugin($name);

            if (! $requiredPlugin || ! $requiredPlugin->isEnabled()) {
                $unsatisfied[$name] = [
                    'version' => null,
                    'constraint' => $versionConstraint,
                ];

                continue;
            }

            if (! Semver::satisfies($requiredPlugin->getVersion(), $versionConstraint)) {
                $unsatisfied[$name] = [
                    'version' => $requiredPlugin->getVersion(),
                    'constraint' => $versionConstraint,
                ];

                continue;
            }
        }

        return $unsatisfied;
    }

    /**
     * Whether the plugin's requirements are satisfied.
     *
     * @param  string|Plugin|array $plugin
     * @return bool
     */
    public function isRequirementsSatisfied($plugin)
    {
        return empty($this->getUnsatisfiedRequirements($plugin));
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

    /**
     * Copy plugin assets.
     *
     * @param Plugin $plugin
     *
     * @return bool
     */
    public function copyPluginAssets($plugin)
    {
        $dir = public_path('plugins/'.$plugin->name);
        Storage::deleteDirectory($dir);

        $this->filesystem->copyDirectory(
            $this->getPluginsDir().DIRECTORY_SEPARATOR.$plugin->name.DIRECTORY_SEPARATOR.'assets',
            $dir.'/assets'
        );
        $this->filesystem->copyDirectory(
            $this->getPluginsDir().DIRECTORY_SEPARATOR.$plugin->name.DIRECTORY_SEPARATOR.'lang',
            $dir.'/lang'
        );
    }

    /**
     * Convert `plugins_enabled` field for backward compatibility.
     *
     * @return $this
     */
    protected function convertPluginRecord()
    {
        $list = collect(json_decode($this->option->get('plugins_enabled'), true));
        $this->enabled = $list->map(function ($item) {
            if (is_string($item)) {
                $plugin = $this->getPlugin($item);

                // If we cannot read the package of that plugin, just return it as-is.
                if (is_null($plugin)) {
                    return $item;
                }

                return [
                    'name' => $item,
                    'version' => $plugin->getVersion(),
                ];
            } else {
                $plugin = $this->getPlugin($item['name']);
                if (! empty($plugin)) {
                    $item['version'] = $plugin->getVersion();
                }

                return $item;
            }
        });

        $this->saveEnabled();

        return $this;
    }
}

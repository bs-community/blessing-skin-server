<?php

namespace App\Services;

use App\Events;
use App\Exceptions\PrettyPageException;
use Composer\Autoload\ClassLoader;
use Composer\Semver\Comparator;
use Composer\Semver\Semver;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PluginManager
{
    protected bool $booted = false;

    protected Application $app;

    protected Option $option;

    protected Dispatcher $dispatcher;

    protected Filesystem $filesystem;

    protected ClassLoader $loader;

    protected ?Collection $plugins;

    protected Collection $enabled;

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
        $this->loader = new ClassLoader();
    }

    public function all(): Collection
    {
        if (isset($this->plugins)) {
            return $this->plugins;
        }

        $this->enabled = collect(json_decode($this->option->get('plugins_enabled', '[]'), true))
            ->reject(fn ($item) => is_string($item))
            ->mapWithKeys(fn ($item) => [$item['name'] => ['version' => $item['version']]]);
        $plugins = collect();
        $versionChanged = [];

        $this->getPluginsDirs()
            ->flatMap(fn ($dir) => $this->filesystem->directories($dir))
            ->unique()
            ->filter(fn ($dir) => $this->filesystem->exists($dir.DIRECTORY_SEPARATOR.'package.json'))
            ->each(function ($directory) use (&$plugins, &$versionChanged) {
                $manifest = json_decode(
                    $this->filesystem->get($directory.DIRECTORY_SEPARATOR.'package.json'),
                    true
                );

                $name = $manifest['name'];
                if ($plugins->has($name)) {
                    throw new PrettyPageException(trans('errors.plugins.duplicate', ['dir1' => $plugins->get($name)->getPath(), 'dir2' => $directory]), 5);
                }

                $plugin = new Plugin($directory, $manifest);
                $plugins->put($name, $plugin);
                if ($this->getUnsatisfied($plugin)->isNotEmpty() || $this->getConflicts($plugin)->isNotEmpty()) {
                    $this->disable($plugin);
                }
                if ($this->enabled->has($name)) {
                    $plugin->setEnabled(true);
                    if (Comparator::notEqualTo(
                        $manifest['version'],
                        $this->enabled->get($name)['version']
                    )) {
                        $this->enabled->put($name, ['version' => $manifest['version']]);
                        $versionChanged[] = $plugin;
                    }
                }
            });

        $this->plugins = $plugins;
        if (count($versionChanged) > 0) {
            $this->saveEnabled();
        }
        array_walk($versionChanged, function ($plugin) {
            $this->dispatcher->dispatch('plugin.versionChanged', [$plugin]);
        });

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

        $this->all()->each(fn ($plugin) => $this->loadViewsAndTranslations($plugin));

        $enabled = $this->getEnabledPlugins();
        $enabled->each(fn ($plugin) => $this->registerPlugin($plugin));
        $this->loader->register();
        $enabled->each(fn ($plugin) => $this->bootPlugin($plugin));
        $this->registerLifecycleHooks();

        $this->booted = true;
    }

    /**
     * Register resources of a plugin.
     */
    public function registerPlugin(Plugin $plugin)
    {
        $this->registerAutoload($plugin);
        $this->loadVendor($plugin);
    }

    /**
     * Boot a plugin.
     */
    public function bootPlugin(Plugin $plugin)
    {
        $this->registerServiceProviders($plugin);
        $this->loadBootstrapper($plugin);
    }

    /**
     * Register classes autoloading.
     */
    protected function registerAutoload(Plugin $plugin)
    {
        $this->loader->addPsr4(
            Str::finish($plugin->namespace, '\\'),
            $plugin->getPath().'/src'
        );
    }

    /**
     * Load Composer dumped autoload file.
     */
    protected function loadVendor(Plugin $plugin)
    {
        $path = $plugin->getPath().'/vendor/autoload.php';
        if ($this->filesystem->exists($path)) {
            $this->filesystem->getRequire($path);
        }
    }

    /**
     * Load views and translations.
     */
    protected function loadViewsAndTranslations(Plugin $plugin)
    {
        $namespace = $plugin->namespace;
        $path = $plugin->getPath();

        $translations = $this->app->make('translation.loader');
        $translations->addNamespace($namespace, $path.'/lang');

        $view = $this->app->make('view');
        $view->addNamespace($namespace, $path.'/views');
    }

    protected function registerServiceProviders(Plugin $plugin)
    {
        $providers = Arr::get($plugin->getManifest(), 'enchants.providers', []);
        array_walk($providers, function ($provider) use ($plugin) {
            $class = (string) Str::of($provider)
                ->finish('ServiceProvider')
                ->start($plugin->namespace.'\\');
            if (class_exists($class)) {
                $this->app->register($class);
            }
        });
    }

    /**
     * Load plugin's bootstrapper.
     */
    protected function loadBootstrapper(Plugin $plugin)
    {
        $path = $plugin->getPath().'/bootstrap.php';
        if ($this->filesystem->exists($path)) {
            try {
                $this->app->call($this->filesystem->getRequire($path), ['plugin' => $plugin]);
            } catch (\Throwable $th) {
                report($th);
                $this->dispatcher->dispatch(new Events\PluginBootFailed($plugin));
                // @codeCoverageIgnoreStart
                if (config('app.debug')) {
                    throw $th;
                }
                // @codeCoverageIgnoreEnd
                if (is_a($th, \Exception::class)) {
                    $handler = $this->app->make(\App\Exceptions\Handler::class);
                    if (!$handler->shouldReport($th)) {
                        throw $th;
                    }
                }
            }
        }
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
                    return $this->app->call($callback, ['plugin' => $plugin]);
                }
            }
        });
    }

    public function get(string $name): ?Plugin
    {
        return $this->all()->get($name);
    }

    /**
     * @return bool|array return `true` if succeeded, or return information if failed
     */
    public function enable($plugin)
    {
        /** @var Plugin|null */
        $plugin = is_string($plugin) ? $this->get($plugin) : $plugin;
        if (empty($plugin)) {
            return false;
        }

        if ($plugin->isEnabled()) {
            return true;
        }

        $unsatisfied = $this->getUnsatisfied($plugin);
        $conflicts = $this->getConflicts($plugin);
        if ($unsatisfied->isNotEmpty() || $conflicts->isNotEmpty()) {
            return compact('unsatisfied', 'conflicts');
        }

        $this->enabled->put($plugin->name, ['version' => $plugin->version]);
        $this->saveEnabled();

        $plugin->setEnabled(true);

        $this->dispatcher->dispatch(new Events\PluginWasEnabled($plugin));

        return true;
    }

    public function disable($plugin)
    {
        /** @var Plugin|null */
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

    public function getEnabledPlugins(): Collection
    {
        return $this->all()->filter(fn ($plugin) => $plugin->isEnabled());
    }

    /**
     * Persist the currently enabled plugins.
     */
    protected function saveEnabled()
    {
        $this->option->set(
            'plugins_enabled',
            $this->enabled
                ->map(fn ($info, $name) => array_merge(['name' => $name], $info))
                ->values()
                ->toJson()
        );
    }

    public function getUnsatisfied(Plugin $plugin)
    {
        return collect(Arr::get($plugin->getManifest(), 'require', []))
            ->mapWithKeys(function ($constraint, $name) {
                if ($name == 'blessing-skin-server') {
                    $version = config('app.version');

                    return (!Semver::satisfies($version, $constraint))
                        ? [$name => compact('version', 'constraint')]
                        : [];
                } elseif ($name == 'php') {
                    preg_match('/(\d+\.\d+\.\d+)/', PHP_VERSION, $matches);
                    $version = $matches[1];

                    return (!Semver::satisfies($version, $constraint))
                        ? [$name => compact('version', 'constraint')]
                        : [];
                } elseif (!$this->enabled->has($name)) {
                    return [$name => ['version' => null, 'constraint' => $constraint]];
                } else {
                    $version = $this->enabled->get($name)['version'];

                    return (!Semver::satisfies($version, $constraint))
                        ? [$name => compact('version', 'constraint')]
                        : [];
                }
            });
    }

    public function getConflicts(Plugin $plugin): Collection
    {
        return collect($plugin->getManifestAttr('enchants.conflicts', []))
            ->mapWithKeys(function ($constraint, $name) {
                $info = $this->enabled->get($name);
                if ($info && Semver::satisfies($info['version'], $constraint)) {
                    return [$name => ['version' => $info['version'], 'constraint' => $constraint]];
                } else {
                    return [];
                }
            });
    }

    /**
     * Format the "unresolved" information into human-readable text.
     */
    public function formatUnresolved(
        Collection $unsatisfied,
        Collection $conflicts
    ): array {
        $unsatisfied = $unsatisfied->map(function ($detail, $name) {
            if ($name === 'blessing-skin-server') {
                $title = 'Blessing Skin Server';
            } elseif ($name === 'php') {
                $title = 'PHP';
            } else {
                $plugin = $this->get($name);
                $title = $plugin ? trans($plugin->title) : $name;
            }

            $constraint = $detail['constraint'];

            return $detail['version']
                ? trans('admin.plugins.operations.unsatisfied.version', compact('title', 'constraint'))
                : trans('admin.plugins.operations.unsatisfied.disabled', ['name' => $title]);
        })->values()->all();

        $conflicts = $conflicts->map(function ($detail, $name) {
            $title = trans($this->get($name)->title);

            return trans('admin.plugins.operations.unsatisfied.conflict', compact('title'));
        })->values()->all();

        return array_merge($unsatisfied, $conflicts);
    }

    public function getPluginsDirs(): Collection
    {
        $config = config('plugins.directory');
        if ($config) {
            return collect(preg_split('/,\s*/', $config))
                ->map(fn ($directory) => realpath($directory) ?: $directory);
        } else {
            return collect([base_path('plugins')]);
        }
    }
}

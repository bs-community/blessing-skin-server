<?php

namespace App\Services;

use App\Events;
use Composer\Semver\Semver;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use App\Exceptions\PrettyPageException;
use Illuminate\Contracts\Events\Dispatcher;
use App\Services\Repositories\OptionRepository;
use Illuminate\Contracts\Foundation\Application;

class PluginManager
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var OptionRepository
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

    public function __construct(
        Application $app,
        OptionRepository $option,
        Dispatcher $dispatcher,
        Filesystem $filesystem
    ) {
        $this->app        = $app;
        $this->option     = $option;
        $this->dispatcher = $dispatcher;
        $this->filesystem = $filesystem;
    }

    /**
     * @return Collection
     */
    public function getPlugins()
    {
        if (is_null($this->plugins)) {
            $plugins = new Collection();

            $installed = [];

            try {
                $resource = opendir($this->getPluginsDir());
            } catch (\Exception $e) {
                throw new PrettyPageException(trans('errors.plugins.directory', ['msg' => $e->getMessage()]), 500);
            }

            // traverse plugins dir
            while($filename = @readdir($resource)) {
                if ($filename == '.' || $filename == '..')
                    continue;

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

                // Per default all plugins are installed if they are registered in composer.
                $plugin->setDirname($dirname);
                $plugin->setInstalled(true);
                $plugin->setNameSpace(Arr::get($package, 'namespace'));
                $plugin->setVersion(Arr::get($package, 'version'));
                $plugin->setEnabled($this->isEnabled($plugin->name));

                if ($plugins->has($plugin->name)) {
                    throw new PrettyPageException(trans('errors.plugins.duplicate', [
                        'dir1' => $plugin->getDirname(),
                        'dir2' => $plugins->get($plugin->name)->getDirname()
                    ]), 5);
                }

                $plugins->put($plugin->name, $plugin);
            }

            $this->plugins = $plugins->sortBy(function ($plugin, $name) {
                return $plugin->name;
            });
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
        if (! $this->isEnabled($name)) {
            $plugin = $this->getPlugin($name);

            $enabled = $this->getEnabled();

            $enabled[] = $name;

            $this->setEnabled($enabled);

            $plugin->setEnabled(true);

            $this->dispatcher->fire(new Events\PluginWasEnabled($plugin));
        }
    }

    /**
     * Disables an plugin.
     *
     * @param string $name
     */
    public function disable($name)
    {
        $enabled = $this->getEnabled();

        if (($k = array_search($name, $enabled)) !== false) {
            unset($enabled[$k]);

            $plugin = $this->getPlugin($name);

            $this->setEnabled($enabled);

            $plugin->setEnabled(false);

            $this->dispatcher->fire(new Events\PluginWasDisabled($plugin));
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

        // fire event before deleting plugin files
        $this->dispatcher->fire(new Events\PluginWasDeleted($plugin));

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
        return (array) json_decode($this->option->get('plugins_enabled'), true);
    }

    /**
     * Persist the currently enabled plugins.
     *
     * @param array $enabled
     */
    protected function setEnabled(array $enabled)
    {
        $enabled = array_values(array_unique($enabled));

        $this->option->set('plugins_enabled', json_encode($enabled));

        // ensure to save options
        $this->option->save();
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
                        'constraint' => $versionConstraint
                    ];
                }

                continue;
            }

            $requiredPlugin = $this->getPlugin($name);

            if (!$requiredPlugin || !$requiredPlugin->isEnabled()) {
                $unsatisfied[$name] = [
                    'version' => null,
                    'constraint' => $versionConstraint
                ];

                continue;
            }

            if (! Semver::satisfies($requiredPlugin->getVersion(), $versionConstraint)) {
                $unsatisfied[$name] = [
                    'version' => $requiredPlugin->getVersion(),
                    'constraint' => $versionConstraint
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
        return config('plugins.directory') ?: base_path('plugins');
    }

}

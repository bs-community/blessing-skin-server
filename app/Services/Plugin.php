<?php

namespace App\Services;

use Illuminate\Support\Arr;

/**
 * @property string $name
 * @property string $description
 * @property string $title
 * @property array  $author
 */
class Plugin
{
    /**
     * The full directory of this plugin.
     *
     * @var string
     */
    protected $path;

    /**
     * The directory name where the plugin installed.
     *
     * @var string
     */
    protected $dirname;

    /**
     * package.json of the package.
     *
     * @var array
     */
    protected $packageInfo;

    /**
     * Whether the plugin is installed.
     *
     * @var bool
     */
    protected $installed = true;

    /**
     * The installed version of the plugin.
     *
     * @var string
     */
    protected $version;

    /**
     * The namespace used by the plugin.
     *
     * @var string
     */
    protected $namespace;

    /**
     * Whether the plugin is enabled.
     *
     * @var bool
     */
    protected $enabled = false;

    /**
     * @param       $path
     * @param array $packageInfo
     */
    public function __construct($path, $packageInfo)
    {
        $this->path = $path;
        $this->packageInfo = $packageInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        return $this->packageInfoAttribute(snake_case($name, '-'));
    }

    /**
     * {@inheritdoc}
     */
    public function __isset($name)
    {
        return isset($this->{$name}) || $this->packageInfoAttribute(snake_case($name, '-'));
    }

    public function packageInfoAttribute($name)
    {
        return Arr::get($this->packageInfo, $name);
    }

    public function assets($relativeUri)
    {
        $baseUrl = config('plugins.url') ?: url('plugins');

        return "$baseUrl/{$this->getDirname()}/assets/$relativeUri";
    }

    /**
     * @param bool $installed
     * @return Plugin
     */
    public function setInstalled($installed)
    {
        $this->installed = $installed;

        return $this;
    }

    public function getDirname()
    {
        return $this->dirname;
    }

    public function setDirname($dirname)
    {
        $this->dirname = $dirname;

        return $this;
    }

    public function getNameSpace()
    {
        return $this->namespace;
    }

    public function setNameSpace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function getViewPath($name)
    {
        return $this->getViewPathByFileName("$name.tpl");
    }

    public function getViewPathByFileName($filename)
    {
        return $this->path."/views/$filename";
    }

    public function getConfigView()
    {
        return $this->hasConfigView() ? view()->file($this->getViewPathByFileName(Arr::get($this->packageInfo, 'config'))) : null;
    }

    public function hasConfigView()
    {
        $filename = Arr::get($this->packageInfo, 'config');

        return $filename && file_exists($this->getViewPathByFileName($filename));
    }

    /**
     * @param string $version
     * @return Plugin
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param array $require
     * @return Plugin
     */
    public function setRequirements($require)
    {
        $this->require = $require;

        return $this;
    }

    /**
     * @return array
     */
    public function getRequirements()
    {
        return (array) $this->require;
    }

    /**
     * @param bool $enabled
     * @return Plugin
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}

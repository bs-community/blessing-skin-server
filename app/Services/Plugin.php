<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Contracts\Support\Arrayable;

/**
 * @property string $name
 * @property string $description
 * @property string $title
 * @property array  $author
 */
class Plugin implements Arrayable
{
    /**
     * The directory of this plugin.
     *
     * @var string
     */
    protected $path;

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
        return $this->packageInfoAttribute(Str::snake($name, '-'));
    }

    /**
     * {@inheritdoc}
     */
    public function __isset($name)
    {
        return isset($this->{$name}) || $this->packageInfoAttribute(Str::snake($name, '-'));
    }

    /**
     * Dot notation getter for composer.json attributes.
     *
     * @see https://laravel.com/docs/5.1/helpers#arrays
     *
     * @param $name
     * @return mixed
     */
    public function packageInfoAttribute($name)
    {
        return Arr::get($this->packageInfo, $name);
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

    /**
     * @return bool
     */
    public function isInstalled()
    {
        return $this->installed;
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
        return $this->path."/views/$name.tpl";
    }

    public function hasConfigView()
    {
        return file_exists($this->getViewPath('config'));
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

    /**
     * Generates an array result for the object.
     *
     * @return array
     */
    public function toArray()
    {
        return (array) array_merge([
            'name'          => $this->name,
            'version'       => $this->getVersion(),
            'path'          => $this->path
        ], $this->packageInfo);
    }
}

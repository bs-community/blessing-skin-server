<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

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

    public function __construct(string $path, array $packageInfo)
    {
        $this->path = $path;
        $this->packageInfo = $packageInfo;
    }

    public function __get(string $name)
    {
        return $this->packageInfoAttribute(Str::snake($name, '-'));
    }

    public function __isset(string $name)
    {
        return isset($this->{$name}) || $this->packageInfoAttribute(snake_case($name, '-'));
    }

    public function packageInfoAttribute(string $name)
    {
        return Arr::get($this->packageInfo, $name);
    }

    public function assets(string $relativeUri): string
    {
        $baseUrl = config('plugins.url') ?: url('plugins');

        return "$baseUrl/{$this->getDirname()}/assets/$relativeUri?v=".$this->version;
    }

    public function setInstalled(bool $installed): self
    {
        $this->installed = $installed;

        return $this;
    }

    public function getDirname(): string
    {
        return $this->dirname;
    }

    public function setDirname(string $dirname): self
    {
        $this->dirname = $dirname;

        return $this;
    }

    public function getNameSpace(): string
    {
        return $this->namespace;
    }

    public function setNameSpace(string $namespace): self
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function getViewPathByFileName(string $filename): string
    {
        return $this->path."/views/$filename";
    }

    public function getConfigView()
    {
        return $this->hasConfigView()
            ? view()->file($this->getViewPathByFileName(Arr::get($this->packageInfo, 'config', 'config.blade.php')))
            : null;
    }

    public function hasConfigView(): bool
    {
        $filename = Arr::get($this->packageInfo, 'config', 'config.blade.php');

        return $filename && file_exists($this->getViewPathByFileName($filename));
    }

    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setRequirements(array $require): self
    {
        $this->require = $require;

        return $this;
    }

    public function getRequirements(): array
    {
        return (array) $this->require;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Plugin
{
    const README_FILES = [
        'README.md',
        'readme.md',
        'README.MD',
    ];

    /** The full path of this plugin. */
    protected string $path;

    /** package.json of the package. */
    protected array $manifest;

    protected $enabled = false;

    public function __construct(string $path, array $manifest)
    {
        $this->path = $path;
        $this->manifest = $manifest;
    }

    public function __get(string $name)
    {
        return $this->getManifestAttr(Str::snake($name, '-'));
    }

    public function getManifest()
    {
        return $this->manifest;
    }

    public function getManifestAttr(string $name, $default = null)
    {
        return Arr::get($this->manifest, $name, $default);
    }

    public function assets(string $relativeUri): string
    {
        $baseUrl = config('plugins.url') ?: url('plugins');

        return "$baseUrl/{$this->name}/assets/$relativeUri?v=".$this->version;
    }

    public function getReadme()
    {
        return Arr::first(
            self::README_FILES,
            fn ($filename) => file_exists($this->path.'/'.$filename)
        );
    }

    public function hasConfig(): bool
    {
        return $this->hasConfigClass() || $this->hasConfigView();
    }

    public function hasConfigClass(): bool
    {
        return Arr::has($this->manifest, 'enchants.config');
    }

    public function getConfigClass()
    {
        $name = Arr::get($this->manifest, 'enchants.config');

        return Str::start($name, $this->manifest['namespace'].'\\');
    }

    public function getViewPath(string $filename): string
    {
        return $this->path."/views/$filename";
    }

    public function getConfigView()
    {
        return $this->hasConfigView()
            ? view()->file($this->getViewPath(Arr::get($this->manifest, 'config', 'config.blade.php')))
            : null;
    }

    public function hasConfigView(): bool
    {
        $filename = Arr::get($this->manifest, 'config', 'config.blade.php');

        return $filename && file_exists($this->getViewPath($filename));
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

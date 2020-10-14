<?php

namespace App\Services\Translations;

use App\Services\Plugin;
use App\Services\PluginManager;
use Illuminate\Cache\Repository;
use Illuminate\Filesystem\Filesystem;

class JavaScript
{
    protected Filesystem $filesystem;

    protected Repository $cache;

    protected PluginManager $plugins;

    protected $prefix = 'front-end-trans-';

    public function __construct(
        Filesystem $filesystem,
        Repository $cache,
        PluginManager $plugins
    ) {
        $this->filesystem = $filesystem;
        $this->cache = $cache;
        $this->plugins = $plugins;
    }

    public function generate(string $locale): string
    {
        $plugins = $this->plugins->getEnabledPlugins();
        $sourceFiles = $plugins
            ->map(fn (Plugin $plugin) => $plugin->getPath()."/lang/$locale/front-end.yml")
            ->filter(fn ($path) => $this->filesystem->exists($path));
        $sourceFiles->push(resource_path("lang/$locale/front-end.yml"));
        $sourceModified = $sourceFiles->max(fn ($path) => $this->filesystem->lastModified($path));

        $compiled = public_path("lang/$locale.js");
        $compiledModified = (int) $this->cache->get($this->prefix.$locale, 0);

        if ($sourceModified > $compiledModified || !$this->filesystem->exists($compiled)) {
            $translations = trans('front-end');
            foreach ($plugins as $plugin) {
                $translations[$plugin->name] = trans($plugin->namespace.'::front-end');
            }

            $content = 'blessing.i18n = '.json_encode($translations, JSON_UNESCAPED_UNICODE);
            $this->filesystem->put($compiled, $content);
            $this->cache->put($this->prefix.$locale, $sourceModified);

            return url()->asset("lang/$locale.js?t=$sourceModified");
        }

        return url()->asset("lang/$locale.js?t=$compiledModified");
    }

    public function resetTime(string $locale): void
    {
        $this->cache->put($this->prefix.$locale, 0);
    }
}

<?php

namespace App\Listeners;

use App\Services\Plugin;
use App\Services\PluginManager;
use Illuminate\Filesystem\Filesystem;

class GeneratePluginTranslations
{
    /** @var Filesystem */
    protected $filesystem;

    /** @var PluginManager */
    protected $plugins;

    public function __construct(Filesystem $filesystem, PluginManager $plugins)
    {
        $this->filesystem = $filesystem;
        $this->plugins = $plugins;
    }

    public function handle()
    {
        $plugins = $this->plugins->getEnabledPlugins();
        $locales = array_keys(config('locales'));

        array_walk($locales, function ($locale) use ($plugins) {
            $i18n = $plugins
                ->filter(function (Plugin $plugin) use ($locale) {
                    return $this->filesystem->exists(
                        $plugin->getPath()."/lang/$locale/front-end.yml"
                    );
                })
                ->map(function (Plugin $plugin) use ($locale) {
                    return trans($plugin->namespace.'::front-end');
                });

            if ($i18n->isNotEmpty()) {
                $content = 'Object.assign(blessing.i18n, '.
                    $i18n->toJson(JSON_UNESCAPED_UNICODE).')';
                $this->filesystem->put(public_path("lang/${locale}_plugin.js"), $content);
            }
        });
    }
}

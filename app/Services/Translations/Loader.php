<?php

namespace App\Services\Translations;

use Spatie\TranslationLoader\TranslationLoaderManager;

class Loader extends TranslationLoaderManager
{
    protected function loadPaths(array $paths, $locale, $group)
    {
        return collect($paths)
            ->reduce(function ($output, $path) use ($locale, $group) {
                if ($this->files->exists($full = "{$path}/{$locale}/{$group}.yml")) {
                    $output = resolve(Yaml::class)->parse($full);
                }

                return $output;
            }, []);
    }
}

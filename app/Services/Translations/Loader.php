<?php

namespace App\Services\Translations;

use Spatie\TranslationLoader\TranslationLoaderManager;

class Loader extends TranslationLoaderManager
{
    protected function loadPath($path, $locale, $group)
    {
        $translations = parent::loadPath($path, $locale, $group);

        $full = "{$path}/{$locale}/{$group}.yml";

        return count($translations) === 0 && $this->files->exists($full)
            ? resolve(Yaml::class)->parse($full)
            : [];
    }
}

<?php

namespace App\Services;

use Devitek\Core\Translation\YamlFileLoader;

class TranslationLoader extends YamlFileLoader
{
    /**
     * Load the messages for the given locale.
     *
     * @param  string  $locale
     * @param  string  $group
     * @param  string  $namespace
     * @return array
     */
    public function load($locale, $group, $namespace = null)
    {
        if (is_null($namespace) || $namespace == '*') {
            // Overrides original translations with custom ones
            return array_replace_recursive(
                $this->loadPath($this->path, $locale, $group),
                $this->loadPathOverrides($locale, $group)
            );
        }

        return $this->loadNamespaced($locale, $group, $namespace);
    }

    /**
     * Load custom messages from /resources/lang/overrides path.
     *
     * @param  string  $locale
     * @param  string  $group
     * @return array
     */
    protected function loadPathOverrides($locale, $group)
    {
        return $this->loadPath("$this->path/overrides", $locale, $group);
    }
}

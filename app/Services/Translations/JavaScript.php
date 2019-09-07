<?php

namespace App\Services\Translations;

use Illuminate\Cache\Repository;
use Illuminate\Filesystem\Filesystem;

class JavaScript
{
    /** @var Filesystem */
    protected $filesystem;

    /** @var Repository */
    protected $cache;

    /** @var Yaml */
    protected $yaml;

    protected $prefix = 'front-end-trans-';

    public function __construct(Filesystem $filesystem, Repository $cache, Yaml $yaml)
    {
        $this->filesystem = $filesystem;
        $this->cache = $cache;
        $this->yaml = $yaml;
    }

    public function generate(string $locale): string
    {
        $source = resource_path("lang/$locale/front-end.yml");
        $compiled = public_path("lang/$locale.js");
        $sourceModified = $this->filesystem->lastModified($source);
        $compiledModified = intval($this->cache->get($this->prefix.$locale, 0));

        if ($sourceModified > $compiledModified || ! $this->filesystem->exists($compiled)) {
            $content = 'blessing.i18n='.json_encode($this->yaml->loadYaml($source), JSON_UNESCAPED_UNICODE);
            $this->filesystem->put($compiled, $content);
            $this->cache->put($this->prefix.$locale, $sourceModified);

            return url("lang/$locale.js?t=$sourceModified");
        }

        return url("lang/$locale.js?t=$compiledModified");
    }
}

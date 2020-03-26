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

    protected $prefix = 'front-end-trans-';

    public function __construct(Filesystem $filesystem, Repository $cache)
    {
        $this->filesystem = $filesystem;
        $this->cache = $cache;
    }

    public function generate(string $locale): string
    {
        $source = resource_path("lang/$locale/front-end.yml");
        $compiled = public_path("lang/$locale.js");
        $sourceModified = $this->filesystem->lastModified($source);
        $compiledModified = intval($this->cache->get($this->prefix.$locale, 0));

        if ($sourceModified > $compiledModified || !$this->filesystem->exists($compiled)) {
            $content = 'blessing.i18n = '.json_encode(trans('front-end'), JSON_UNESCAPED_UNICODE);
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

    public function plugin(string $locale): string
    {
        $path = public_path("lang/${locale}_plugin.js");
        if ($this->filesystem->exists($path)) {
            $lastModified = $this->filesystem->lastModified($path);

            return url()->asset("lang/${locale}_plugin.js?t=$lastModified");
        }

        return '';
    }
}

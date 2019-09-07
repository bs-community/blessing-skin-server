<?php

namespace App\Services\Translations;

use Illuminate\Contracts\Cache\Repository;
use Symfony\Component\Yaml\Yaml as YamlParser;

class Yaml
{
    /** @var Repository */
    protected $cache;

    public function __construct(Repository $cache)
    {
        $this->cache = $cache;
    }

    public function parse(string $path): array
    {
        $key = 'yaml-trans-'.md5($path).'-'.filemtime($path);

        return $this->cache->rememberForever($key, function () use ($path) {
            return YamlParser::parseFile($path);
        });
    }
}

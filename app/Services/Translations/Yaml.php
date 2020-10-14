<?php

namespace App\Services\Translations;

use Illuminate\Contracts\Cache\Repository;
use Symfony\Component\Yaml\Yaml as YamlParser;

class Yaml
{
    protected Repository $cache;

    protected $prefix = 'yaml-trans-';

    public function __construct(Repository $cache)
    {
        $this->cache = $cache;
    }

    public function parse(string $path)
    {
        $prefix = $this->prefix.md5($path).'-';
        $cacheTime = intval($this->cache->get($prefix.'time', 0));
        $fileTime = filemtime($path);

        if ($fileTime > $cacheTime) {
            $content = YamlParser::parseFile($path);
            $this->cache->put($prefix.'content', $content);
            $this->cache->put($prefix.'time', $fileTime);

            return $content;
        }

        return $this->cache->get($prefix.'content', []);
    }
}

<?php

namespace Tests;

use App\Services\Translations\Yaml;
use Illuminate\Contracts\Cache\Repository;

class YamlTest extends TestCase
{
    public function testParse()
    {
        $path = resource_path('lang/en/general.yml');
        $prefix = 'yaml-trans-'.md5($path).'-';
        $this->mock(Repository::class, function ($mock) use ($prefix, $path) {
            $mock->shouldReceive('get')
                ->with($prefix.'time', 0)
                ->twice()
                ->andReturn(0, filemtime($path) + 100000);
            $mock->shouldReceive('put')->twice();
            $mock->shouldReceive('get')
                ->with($prefix.'content', [])
                ->once()
                ->andReturn([]);
        });

        $this->app->make(Yaml::class)->parse($path);
        $this->app->make(Yaml::class)->parse($path);
    }
}

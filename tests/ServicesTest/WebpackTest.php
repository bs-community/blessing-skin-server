<?php

namespace Tests;

use File;
use App\Services\Webpack;

class WebpackTest extends TestCase
{
    public function testManifest()
    {
        File::shouldReceive('exists')->andReturn(true);
        File::shouldReceive('get')->andReturn(json_encode(['a' => 'b']));
        $key = 'a';
        $this->assertEquals('http://localhost/app/b', app('webpack')->$key);
    }
}

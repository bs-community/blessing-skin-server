<?php

namespace Tests;

use File;

class WebpackTest extends TestCase
{
    public function testManifest()
    {
        File::shouldReceive('exists')->andReturn(true);
        File::shouldReceive('get')->andReturn(json_encode(['a' => 'b']));
        $key = 'a';
        $this->assertEquals('b', app('webpack')->$key);
    }
}

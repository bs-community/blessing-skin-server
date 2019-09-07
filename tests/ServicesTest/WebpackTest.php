<?php

namespace Tests;

use Illuminate\Filesystem\Filesystem;

class WebpackTest extends TestCase
{
    public function testManifest()
    {
        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('exists')
                ->with(public_path('app/manifest.json'))
                ->once()
                ->andReturn(true);

            $mock->shouldReceive('get')
                ->with(public_path('app/manifest.json'))
                ->once()
                ->andReturn(json_encode(['a' => 'b']));
        });
        $this->assertEquals('b', resolve(\App\Services\Webpack::class)->{'a'});
        $this->assertEquals('', resolve(\App\Services\Webpack::class)->{'nope'});
    }
}

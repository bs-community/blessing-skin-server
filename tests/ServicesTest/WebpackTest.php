<?php

namespace Tests;

use App\Services\Webpack;
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

        $this->app->forgetInstance(Webpack::class);
        $webpack = $this->app->make(Webpack::class);
        $this->assertEquals('b', $webpack->{'a'});
        $this->assertEquals('', $webpack->{'nope'});
    }

    public function testUrl()
    {
        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('exists')
                ->with(public_path('app/manifest.json'))
                ->twice()
                ->andReturn(true);

            $mock->shouldReceive('get')
                ->with(public_path('app/manifest.json'))
                ->twice()
                ->andReturn(json_encode(['a' => 'b']));
        });

        $this->app->forgetInstance(Webpack::class);
        $webpack = $this->app->make(Webpack::class);
        $this->assertEquals('http://localhost/app/b', $webpack->url('a'));

        $this->mock(\App\Services\Option::class, function ($mock) {
            $mock->shouldReceive('get')
                ->with('cdn_address')
                ->once()
                ->andReturn('http://cdn.test');
        });
        $this->app->forgetInstance(Webpack::class);
        $webpack = $this->app->make(Webpack::class);
        $this->assertEquals('http://cdn.test/app/b', $webpack->url('a'));

        config(['app.asset.env' => 'development']);
        $this->assertEquals('http://localhost:8080/a', $webpack->url('a'));
    }
}

<?php

namespace Tests;

use App\Services\Plugin;
use App\Services\PluginManager;
use App\Services\Translations\JavaScript;
use Illuminate\Cache\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class JavaScriptTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        app()->forgetInstance(JavaScript::class);
    }

    public function testGenerateFreshFile()
    {
        $this->mock(PluginManager::class, function ($mock) {
            $mock->shouldReceive('getEnabledPlugins')
                ->with()
                ->once()
                ->andReturn(collect([new Plugin('/reina', ['namespace' => 'れいな'])]));
        });
        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('exists')
                ->with('/reina/lang/en/front-end.yml')
                ->once()
                ->andReturn(true);
            $mock->shouldReceive('lastModified')
                ->with(resource_path('lang/en/front-end.yml'))
                ->once()
                ->andReturn(1);
            $mock->shouldReceive('lastModified')
                ->with('/reina/lang/en/front-end.yml')
                ->once()
                ->andReturn(2);
            $mock->shouldReceive('put')
                ->withArgs(function ($path, $content) {
                    $this->assertEquals(public_path('lang/en.js'), $path);
                    $this->assertTrue(Str::startsWith($content, 'blessing.i18n'));
                    $this->assertStringContainsString('"れいな::front-end"', $content);

                    return true;
                })
                ->once()
                ->andReturn(1);
        });
        $this->mock(Repository::class, function ($mock) {
            $mock->shouldReceive('get')
                ->with('front-end-trans-en', 0)
                ->once()
                ->andReturn(0);
            $mock->shouldReceive('put')
                ->with('front-end-trans-en', 2)
                ->once();
        });

        $this->assertEquals(
            url('lang/en.js?t=2'),
            resolve(JavaScript::class)->generate('en')
        );
    }

    public function testGenerateCached()
    {
        $this->mock(PluginManager::class, function ($mock) {
            $mock->shouldReceive('getEnabledPlugins')
                ->with()
                ->once()
                ->andReturn(collect([new Plugin('/reina', ['namespace' => 'れいな'])]));
        });
        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('exists')
                ->with('/reina/lang/en/front-end.yml')
                ->once()
                ->andReturn(true);
            $mock->shouldReceive('lastModified')
                ->with(resource_path('lang/en/front-end.yml'))
                ->once()
                ->andReturn(1);
            $mock->shouldReceive('lastModified')
                ->with('/reina/lang/en/front-end.yml')
                ->once()
                ->andReturn(2);
            $mock->shouldReceive('exists')
                ->with(public_path('lang/en.js'))
                ->once()
                ->andReturn(true);
        });
        $this->mock(Repository::class, function ($mock) {
            $mock->shouldReceive('get')
                ->with('front-end-trans-en', 0)
                ->once()
                ->andReturn(2);
        });

        $this->assertEquals(
            url('lang/en.js?t=2'),
            resolve(JavaScript::class)->generate('en')
        );
    }

    public function testResetTime()
    {
        $this->spy(Repository::class, function ($spy) {
            $spy->shouldReceive('put')
                ->with('front-end-trans-en', 0)
                ->once();
        });

        resolve(JavaScript::class)->resetTime('en');
    }

    public function testFallbackLocale()
    {
        $this->get('/', ['Accept-Language' => 'xyz'])
            ->assertSuccessful()
            ->assertSee('lang/en.js');
    }
}

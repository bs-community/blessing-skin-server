<?php

namespace Tests;

use Illuminate\Cache\Repository;
use App\Services\Translations\Yaml;
use Illuminate\Filesystem\Filesystem;
use App\Services\Translations\JavaScript;

class JavaScriptTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->forgetInstance(JavaScript::class);
    }

    public function testGenerateFreshFile()
    {
        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('lastModified')
                ->with(resource_path('lang/en/front-end.yml'))
                ->once()
                ->andReturn(1);
            $mock->shouldReceive('put')
                ->with(public_path('lang/en.js'), 'blessing.i18n={"a":"b"}')
                ->once()
                ->andReturn(1);
        });
        $this->mock(Repository::class, function ($mock) {
            $mock->shouldReceive('get')
                ->with('front-end-trans-en', 0)
                ->once()
                ->andReturn(0);
            $mock->shouldReceive('put')
                ->with('front-end-trans-en', 1)
                ->once();
        });
        $this->mock(Yaml::class, function ($mock) {
            $mock->shouldReceive('loadYaml')
                ->with(resource_path('lang/en/front-end.yml'))
                ->once()
                ->andReturn(['a' => 'b']);
        });

        $this->assertEquals(url('lang/en.js?t=1'), resolve(JavaScript::class)->generate('en'));
    }

    public function testGenerateCached()
    {
        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('lastModified')
                ->with(resource_path('lang/en/front-end.yml'))
                ->once()
                ->andReturn(1);
            $mock->shouldReceive('exists')
                ->with(public_path('lang/en.js'))
                ->once()
                ->andReturn(true);
        });
        $this->mock(Repository::class, function ($mock) {
            $mock->shouldReceive('get')
                ->with('front-end-trans-en', 0)
                ->once()
                ->andReturn(1);
        });

        $this->assertEquals(url('lang/en.js?t=1'), resolve(JavaScript::class)->generate('en'));
    }
}

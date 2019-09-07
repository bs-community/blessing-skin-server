<?php

namespace Tests;

use App\Services\Plugin;
use App\Services\PluginManager;
use App\Events\PluginWasEnabled;
use Illuminate\Filesystem\Filesystem;

class GeneratePluginTranslationsTest extends TestCase
{
    public function testHandle()
    {
        config(['locales' => ['en' => [], 'jp' => []]]);
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
                ->andReturn(false);
            $mock->shouldReceive('exists')
                ->with('/reina/lang/jp/front-end.yml')
                ->once()
                ->andReturn(true);
            $mock->shouldReceive('put')
                ->with(
                    public_path('lang/jp_plugin.js'),
                    'Object.assign(blessing.i18n, ["れいな::front-end"])'
                )
                ->once();
        });

        $this->app->call('App\Listeners\GeneratePluginTranslations@handle');
    }
}

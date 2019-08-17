<?php

namespace Tests;

use App\Services\Plugin;
use App\Services\PluginManager;

class PluginEnableCommandTest extends TestCase
{
    public function testEnablePlugin()
    {
        $this->mock(PluginManager::class, function ($mock) {
            $mock->shouldReceive('get')
                ->with('my-plugin')
                ->once()
                ->andReturn(new Plugin('', ['title' => 'My Plugin']));
            $mock->shouldReceive('enable')->with('nope')->once()->andReturn(false);
            $mock->shouldReceive('enable')->with('my-plugin')->once()->andReturn(true);
        });

        $this->artisan('plugin:enable nope')
            ->expectsOutput(trans('admin.plugins.operations.not-found'));
        $this->artisan('plugin:enable my-plugin')
            ->expectsOutput(trans('admin.plugins.operations.enabled', ['plugin' => 'My Plugin']));
    }
}

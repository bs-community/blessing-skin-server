<?php

namespace Tests;

use App\Services\Plugin;
use App\Services\PluginManager;

class PluginDisableCommandTest extends TestCase
{
    public function testDisablePlugin()
    {
        $this->mock(PluginManager::class, function ($mock) {
            $mock->shouldReceive('get')->with('nope')->once()->andReturn(null);
            $mock->shouldReceive('get')
                ->with('my-plugin')
                ->once()
                ->andReturn(new Plugin('', ['title' => 'My Plugin']));
            $mock->shouldReceive('disable')->with('my-plugin')->once();
        });

        $this->artisan('plugin:disable nope')
            ->expectsOutput(trans('admin.plugins.operations.not-found'));
        $this->artisan('plugin:disable my-plugin')
            ->expectsOutput(trans('admin.plugins.operations.disabled', ['plugin' => 'My Plugin']));
    }
}

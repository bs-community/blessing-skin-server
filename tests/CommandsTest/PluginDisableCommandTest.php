<?php

namespace Tests;

use App\Services\PluginManager;

class PluginDisableCommandTest extends TestCase
{
    public function testDisablePlugin()
    {
        $this->mock(PluginManager::class, function ($mock) {
            $mock->shouldReceive('disable')->with('my-plugin')->once();
        });
        $this->artisan('plugin:disable my-plugin');
    }
}

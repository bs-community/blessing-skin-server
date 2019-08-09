<?php

namespace Tests;

use App\Services\PluginManager;

class PluginEnableCommandTest extends TestCase
{
    public function testEnablePlugin()
    {
        $this->mock(PluginManager::class, function ($mock) {
            $mock->shouldReceive('enable')->with('my-plugin')->once();
        });
        $this->artisan('plugin:enable my-plugin');
    }
}

<?php

namespace Tests;

use App\Events;
use App\Models\User;
use App\Services\Plugin;

class NotifyFailedPluginTest extends TestCase
{
    public function testHandle()
    {
        $content = [];
        $plugin = new Plugin('', ['title' => 'ff']);

        event(new Events\PluginBootFailed($plugin));
        event(new Events\RenderingFooter($content));
        $this->assertCount(0, $content);

        $this->actingAs(User::factory()->make());
        event(new Events\PluginBootFailed($plugin));
        event(new Events\RenderingFooter($content));
        $this->assertCount(0, $content);

        $this->actingAs(User::factory()->admin()->make());
        event(new Events\PluginBootFailed($plugin));
        event(new Events\RenderingFooter($content));
        $this->assertStringContainsString('blessing.notify.toast', $content[0]);
    }
}

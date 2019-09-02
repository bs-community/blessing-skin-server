<?php

namespace Tests;

use Eventy;

class FilterTest extends TestCase
{
    public function testAddFilter()
    {
        $this->mock('eventy', function ($mock) {
            $mock->shouldReceive('addFilter')
                ->withArgs(function ($hook, $callback) {
                    $this->assertEquals('my.hook', $hook);
                    $this->assertEquals('Filtered text', $callback('text'));

                    return true;
                })
                ->once();
        });
        add_filter('my.hook', function ($value) {
            return "Filtered $value";
        });
    }

    public function testIntegration()
    {
        add_filter('hook.test', function ($value) {
            return $value.'ed';
        });
        $this->assertEquals('tested', Eventy::filter('hook.test', 'test'));
    }
}

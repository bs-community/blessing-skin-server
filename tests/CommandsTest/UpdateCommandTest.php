<?php

namespace Tests;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;

class UpdateCommandTest extends TestCase
{
    use DatabaseTransactions;

    public function testUpdate()
    {
        Event::fake();
        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('put')
                ->with(storage_path('install.lock'), '')
                ->once()
                ->andReturn(true);
        });
        Cache::partialMock()->shouldReceive('flush')->once();
        option(['version' => '0.0.0']);
        config([
            'app.version' => '0.0.1',
            'translation-loader.translation_loaders' => [],
        ]);

        $this->artisan('update')
            ->expectsOutput(trans('setup.updates.success.title'));
        $this->assertEquals('0.0.1', option('version'));
        Event::assertDispatched('__0.0.1');
    }
}

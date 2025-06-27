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

        /*
         * Yeah I know it's FUCKING UGLY
         * But it's the only FUCKING way that WORKS
         * SOMEONE REFACTOR THIS SHIT PLEASE, I BEG
         */
        Cache::partialMock()->shouldReceive('flush')->once();
        $mock = \Mockery::mock(\Illuminate\Contracts\Cache\Repository::class);
        $mock->shouldReceive('put');
        $mock->shouldReceive('get');
        $this->app->instance('cache.store', $mock);

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

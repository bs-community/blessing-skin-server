<?php

namespace Tests;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UpdateCommandTest extends TestCase
{
    use DatabaseTransactions;

    public function testUpdate()
    {
        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('exists')
                ->with(storage_path('install.lock'))
                ->andReturn(true);

            $mock->shouldReceive('put')
                ->with(storage_path('install.lock'), '')
                ->once()
                ->andReturn(true);

            $mock->shouldReceive('files')
                ->with(database_path('update_scripts'))
                ->once()
                ->andReturn([]);
        });
        config(['app.version' => '100.0.0']);

        $this->artisan('update')
            ->expectsOutput(trans('setup.updates.success.title'));
        $this->assertEquals('100.0.0', option('version'));
    }
}

<?php

namespace Tests;

use App\Models\User;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CheckInstallationTest extends TestCase
{
    use DatabaseTransactions;

    public function testHandle()
    {
        $this->get('/setup')->assertSee('Already installed');

        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('exists')
                ->with(storage_path('install.lock'))
                ->twice()
                ->andReturn(false);
            $mock->shouldReceive('exists')
                ->with(base_path('.env'))
                ->andReturn(true);
        });
        $this->get('/setup')->assertSee(trans(
            'setup.wizard.welcome.text',
            ['version' => config('app.version')]
        ));

        $this->actingAs(factory(User::class)->states('superAdmin')->make());
        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('exists')
                ->with(storage_path('install.lock'))
                ->andReturn(true);
        });
        config(['app.version' => '100.0.0']);
        $this->get('/setup/update')->assertSee(trans('setup.updates.welcome.title'));
    }
}

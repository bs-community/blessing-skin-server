<?php

namespace Tests;

use Schema;
use App\Models\User;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BsInstallCommandTest extends TestCase
{
    use DatabaseTransactions;

    public function testInstallation()
    {
        $this->artisan('bs:install ibara.mayaka@hyouka.test 12345678 mayaka')
            ->expectsOutput('You have installed Blessing Skin Server. Nothing to do.');

        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('exists')
                ->with(storage_path('install.lock'))
                ->once()
                ->andReturn(false);
            $mock->shouldReceive('put')
                ->with(storage_path('install.lock'), '')
                ->once()
                ->andReturn(true);
        });

        $this->artisan('bs:install ibara.mayaka@hyouka.test 12345678 mayaka')
            ->expectsOutput('Installation completed!');
        $this->assertEquals(url('/'), option('site_url'));
        $user = User::first();
        $this->assertEquals('ibara.mayaka@hyouka.test', $user->email);
        $this->assertTrue($user->verifyPassword('12345678'));
        $this->assertEquals('mayaka', $user->nickname);
        $this->assertEquals(User::SUPER_ADMIN, $user->permission);
    }
}

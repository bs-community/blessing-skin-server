<?php

namespace Tests;

use Schema;
use Artisan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BsInstallCommandTest extends TestCase
{
    use DatabaseTransactions;

    public function testInstallation()
    {
        $this->artisan('bs:install ibara.mayaka@hyouka.test 12345678 mayaka')
            ->expectsOutput('You have installed Blessing Skin Server. Nothing to do.');

        $tables = [
            'user_closet',
            'migrations',
            'options',
            'players',
            'textures',
            'users',
            'reports',
            'oauth_auth_codes',
            'oauth_access_tokens',
            'oauth_clients',
            'oauth_personal_access_clients',
            'oauth_refresh_tokens',
            'notifications',
            'jobs',
        ];
        array_walk($tables, function ($table) {
            Schema::dropIfExists($table);
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

<?php

namespace Tests;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Finder\SplFileInfo;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SetupControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testWelcome()
    {
        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('exists')
                ->with(storage_path('install.lock'))
                ->andReturn(false);
            $mock->shouldReceive('exists')
                ->with(base_path('.env'))
                ->once()
                ->andReturn(false);
            $mock->shouldReceive('copy')
                ->with(base_path('.env.example'), base_path('.env'))
                ->once()
                ->andReturn(true);
        });
        $this->get('/setup')->assertViewIs('setup.wizard.welcome');
    }

    public function testDatabase()
    {
        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('exists')
                ->with(storage_path('install.lock'))
                ->atLeast(1)
                ->andReturn(false);
            $mock->shouldReceive('put')
                ->with(storage_path('install.lock'), '')
                ->atLeast(1)
                ->andReturn(true);
        });

        $fake = [
            'type' => env('DB_CONNECTION'),
            'host' => env('DB_HOST'),
            'port' => env('DB_PORT'),
            'db' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'prefix' => '',
        ];
        File::shouldReceive('get')->with(base_path('.env'))->andReturn('');
        File::shouldReceive('put')->with(base_path('.env'), '');
        $this->post('/setup/database', $fake)->assertRedirect('/setup/info');

        $this->get('/setup/database')->assertRedirect('/setup/info');
    }

    public function testReportDatabaseConnectionError()
    {
        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('exists')
                ->with(storage_path('install.lock'))
                ->atLeast(1)
                ->andReturn(false);
            $mock->shouldReceive('put')
                ->with(storage_path('install.lock'), '')
                ->atLeast(1)
                ->andReturn(true);
        });

        $this->post('/setup/database', ['type' => 'sqlite', 'host' => 'placeholder', 'db' => 'test'])
            ->assertSee(trans('setup.database.connection-error', [
                'type' => 'SQLite',
                'msg' => 'Database (test) does not exist.',
            ]));
    }

    public function testFinish()
    {
        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('exists')
                ->with(storage_path('install.lock'))
                ->atLeast(1)
                ->andReturn(false);
            $mock->shouldReceive('put')
                ->with(storage_path('install.lock'), '')
                ->atLeast(1)
                ->andReturn(true);
        });

        // Without `email` field
        $this->post('/setup/finish')
            ->assertDontSee(trans('setup.wizard.finish.title'));

        // Not an valid email address
        $this->post('/setup/finish', ['email' => 'not_an_email'])
            ->assertDontSee(trans('setup.wizard.finish.title'));

        // Empty nickname
        $this->post('/setup/finish', [
            'email' => 'a@b.c',
        ])->assertDontSee(trans('setup.wizard.finish.title'));

        // Invalid characters in nickname
        $this->post('/setup/finish', [
            'email' => 'a@b.c',
            'nickname' => '\\',
        ])->assertDontSee(trans('setup.wizard.finish.title'));

        // Too long nickname
        $this->post('/setup/finish', [
            'email' => 'a@b.c',
            'nickname' => Str::random(256),
        ])->assertDontSee(trans('setup.wizard.finish.title'));

        // Without `password` field
        $this->post('/setup/finish', [
            'email' => 'a@b.c',
            'nickname' => 'nickname',
        ])->assertDontSee(trans('setup.wizard.finish.title'));

        // Password is too short
        $this->post('/setup/finish', [
            'email' => 'a@b.c',
            'nickname' => 'nickname',
            'password' => '1',
        ])->assertDontSee(trans('setup.wizard.finish.title'));

        // Password is too long
        $this->post('/setup/finish', [
            'email' => 'a@b.c',
            'nickname' => 'nickname',
            'password' => Str::random(17),
        ])->assertDontSee(trans('setup.wizard.finish.title'));

        // Confirmation is not OK
        $this->post('/setup/finish', [
            'email' => 'a@b.c',
            'nickname' => 'nickname',
            'password' => '12345678',
            'password_confirmation' => '12345679',
        ])->assertDontSee(trans('setup.wizard.finish.title'));

        // Without `site_name` field
        $this->post('/setup/finish', [
            'email' => 'a@b.c',
            'nickname' => 'nickname',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ])->assertDontSee(trans('setup.wizard.finish.title'));

        // Regenerate keys
        Artisan::shouldReceive('call')
            ->with('key:generate')
            ->once()
            ->andReturn(true);
        Artisan::shouldReceive('call')
            ->with('salt:random')
            ->once()
            ->andReturn(true);
        Artisan::shouldReceive('call')
            ->with('jwt:secret', ['--no-interaction' => true])
            ->once()
            ->andReturn(true);
        Artisan::shouldReceive('call')
            ->with('passport:keys', ['--no-interaction' => true])
            ->once()
            ->andReturn(true);
        Artisan::shouldReceive('call')
            ->with('migrate', [
                '--force' => true,
                '--path' => [
                    'database/migrations',
                    'vendor/laravel/passport/database/migrations',
                ],
            ])
            ->once()
            ->andReturn(true);
        $this->post('/setup/finish', [
            'email' => 'a@b.c',
            'nickname' => 'nickname',
            'password' => '12345678',
            'password_confirmation' => '12345678',
            'site_name' => 'bs',
            'generate_random' => true,
        ])->assertSee(trans('setup.wizard.finish.title'))
            ->assertSee('a@b.c')
            ->assertSee('12345678');
        $superAdmin = \App\Models\User::find(1);
        $this->assertEquals('a@b.c', $superAdmin->email);
        $this->assertTrue($superAdmin->verifyPassword('12345678'));
        $this->assertEquals('nickname', $superAdmin->nickname);
        $this->assertEquals('bs', option('site_name'));
    }

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
                ->andReturn([
                    new SplFileInfo('/1.0.0.php', '', ''),
                    new SplFileInfo('/99.0.0.php', '', ''),
                    new SplFileInfo('/100.0.0.php', '', ''),
                ]);

            $mock->shouldNotReceive('getRequire')->with('/1.0.0.php');

            $mock->shouldReceive('getRequire')
                ->with('/99.0.0.php')
                ->once();

            $mock->shouldReceive('getRequire')
                ->with('/100.0.0.php')
                ->once();
        });
        Artisan::shouldReceive('call')->with('view:clear')->once();
        config(['app.version' => '100.0.0']);

        $this->actAs('superAdmin')
            ->get('/setup/exec-update')
            ->assertViewIs('setup.updates.success');
        $this->assertEquals('100.0.0', option('version'));
    }
}

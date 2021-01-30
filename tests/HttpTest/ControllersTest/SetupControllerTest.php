<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel as Artisan;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;

class SetupControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
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

            $mock->shouldReceive('get')
                ->with(base_path('.env'))
                ->once()
                ->andReturn('DB_CONNECTION=abc');
            $mock->shouldReceive('put')
                ->with(base_path('.env'), 'DB_CONNECTION='.env('DB_CONNECTION'))
                ->once()
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
        $this->post('/setup/database', $fake)->assertRedirect('/setup/info');
        $this->get('/setup/database')->assertRedirect('/setup/info');

        $this->mock(\Illuminate\Database\DatabaseManager::class, function ($mock) {
            $mock->shouldReceive('connection')->andThrow(new \Exception())->once();
        });
        $this->post('/setup/database', ['type' => 'sqlite'])
            ->assertSee(
                trans('setup.database.connection-error', ['type' => 'SQLite', 'msg' => ''])
            );

        $this->mock(\Illuminate\Database\Connection::class, function ($mock) {
            $mock->shouldReceive('getPdo')->andThrow(new \Exception());
        });
        $this->get('/setup/database')->assertViewIs('setup.wizard.database');
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

        $this->spy(Artisan::class, function ($spy) {
            $spy->shouldReceive('call')
                ->with('passport:keys', ['--no-interaction' => true])
                ->once();
            $spy->shouldReceive('call')
                ->with('migrate', [
                    '--force' => true,
                    '--path' => [
                        'database/migrations',
                        'vendor/laravel/passport/database/migrations',
                    ],
                ])
                ->once();
        });
        $this->post('/setup/finish', [
            'email' => 'a@b.c',
            'nickname' => 'nickname',
            'password' => '12345678',
            'password_confirmation' => '12345678',
            'site_name' => 'bs',
        ])->assertSee(trans('setup.wizard.finish.title'));
        $superAdmin = \App\Models\User::find(1);
        $this->assertEquals('a@b.c', $superAdmin->email);
        $this->assertTrue($superAdmin->verifyPassword('12345678'));
        $this->assertEquals('nickname', $superAdmin->nickname);
        $this->assertEquals('bs', option('site_name'));
    }
}

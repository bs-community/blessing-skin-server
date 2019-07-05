<?php

namespace Tests;

use DB;
use Mockery;
use Exception;
use CreateAllTables;
use Illuminate\Support\Str;
use AddVerificationToUsersTable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SetupControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        $this->dropAllTables();
        Mockery::close();
        parent::tearDown();
    }

    protected function dropAllTables()
    {
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

        return $this;
    }

    public function testWelcome()
    {
        $this->dropAllTables();
        $this->get('/setup')->assertViewIs('setup.wizard.welcome');
    }

    public function testDatabase()
    {
        $this->dropAllTables();
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
        $this->dropAllTables();
        $this->post('/setup/database', ['type' => 'sqlite', 'host' => 'placeholder', 'db' => 'test'])
            ->assertSee(trans('setup.database.connection-error', [
                'type' => 'SQLite',
                'msg' => 'Database (test) does not exist.',
            ]));
    }

    public function testInfo()
    {
        $this->dropAllTables();
        $this->get('/setup/info')->assertViewIs('setup.wizard.info');
        Artisan::call('migrate:refresh');
        Schema::drop('users');
        $this->get('/setup/info')->assertSee('already exist');
    }

    public function testFinish()
    {
        $this->dropAllTables();
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
            ->with('key:random')
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
                    'vendor/laravel/passport/database/migrations'
                ]
            ])
            ->once()
            ->andReturnUsing(function () {
                $migration = new CreateAllTables();
                $migration->up();

                $migration = new AddVerificationToUsersTable();
                $migration->up();
            });
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
        $this->actAs('superAdmin')
            ->get('/setup/update')
            ->assertSee(trans('setup.locked.text'));

        option(['version' => '0.1.0']);
        $this->get('/setup/update')
            ->assertSee(trans('setup.updates.welcome.title'));
    }

    public function testDoUpdate()
    {
        $current_version = config('app.version');
        config(['app.version' => '100.0.0']);
        copy(
            database_path('update_scripts/update-3.1-to-3.1.1.php'),
            database_path("update_scripts/update-$current_version-to-100.0.0.php")
        );    // Just a fixture

        config(['options.new_option' => 'value']);
        $this->actAs('superAdmin')->get('/setup/exec-update')->assertViewHas('tips');
        $this->assertEquals('value', option('new_option'));
        $this->assertEquals('100.0.0', option('version'));
        unlink(database_path("update_scripts/update-$current_version-to-100.0.0.php"));

        option(['version' => '3.0.0']);   // Fake old version
        $this->get('/setup/exec-update');
        $this->assertEquals('100.0.0', option('version'));
    }

    public function testCheckDirectories()
    {
        Storage::shouldReceive('disk')
            ->with('root')
            ->andReturnSelf();
        Storage::shouldReceive('has')
            ->with('storage/textures')
            ->andReturn(false);
        Storage::shouldReceive('makeDirectory')
            ->with('storage/textures')
            ->andThrow(new Exception());

        $this->assertFalse(\App\Http\Controllers\SetupController::checkDirectories());
    }
}

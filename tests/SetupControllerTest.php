<?php

namespace Tests;

use Mockery;
use Exception;
use CreateAllTables;
use AddVerificationToUsersTable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SetupControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp()
    {
        parent::setUp();
        $this->dropAllTables();
    }

    protected function tearDown()
    {
        $this->dropAllTables();
        Mockery::close();
        parent::tearDown();
    }

    protected function dropAllTables()
    {
        $tables = [
            'closets', 'migrations', 'options', 'players', 'textures', 'users'
        ];
        array_walk($tables, function ($table) {
            Schema::dropIfExists($table);
        });

        return $this;
    }

    public function testWelcome()
    {
        $this->get('/setup')->assertViewIs('setup.wizard.welcome');
    }

    public function testDatabase()
    {
        $fake = [
            'type' => 'mysql',
            'host' => env('DB_HOST'),
            'port' => '3306',
            'db' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'prefix' => '',
        ];
        File::shouldReceive('get')->with('.env')->andReturn('');
        File::shouldReceive('put')->with('.env', '');
        $this->post('/setup/database', $fake)->assertRedirect('/setup/info');
    }

    public function testReportDatabaseConnectionError()
    {
        $this->post('/setup/database', ['type' => 'sqlite', 'host' => 'placeholder', 'db' => 'test'])
            ->assertSee(trans('setup.database.connection-error', [
                'type' => 'SQLite',
                'msg' => 'Database (test) does not exist.'
            ]));
    }

    public function testInfo()
    {
        $this->get('/setup/info')
            ->assertViewIs('setup.wizard.info');

        Artisan::call('migrate:refresh');
        Schema::drop('users');
        $this->get('/setup/info')->assertSee('already exist');
    }

    public function testFinish()
    {
        // Without `email` field
        $this->post('/setup/finish')
            ->assertDontSee(trans('setup.wizard.finish.title'));

        // Not an valid email address
        $this->post('/setup/finish', ['email' => 'not_an_email'])
            ->assertDontSee(trans('setup.wizard.finish.title'));

        // Empty nickname
        $this->post('/setup/finish', [
            'email' => 'a@b.c'
        ])->assertDontSee(trans('setup.wizard.finish.title'));

        // Invalid characters in nickname
        $this->post('/setup/finish', [
            'email' => 'a@b.c',
            'nickname' => '\\'
        ])->assertDontSee(trans('setup.wizard.finish.title'));

        // Too long nickname
        $this->post('/setup/finish', [
            'email' => 'a@b.c',
            'nickname' => str_random(256)
        ])->assertDontSee(trans('setup.wizard.finish.title'));

        // Without `password` field
        $this->post('/setup/finish', [
            'email' => 'a@b.c',
            'nickname' => 'nickname'
        ])->assertDontSee(trans('setup.wizard.finish.title'));

        // Password is too short
        $this->post('/setup/finish', [
            'email' => 'a@b.c',
            'nickname' => 'nickname',
            'password' => '1'
        ])->assertDontSee(trans('setup.wizard.finish.title'));

        // Password is too long
        $this->post('/setup/finish', [
            'email' => 'a@b.c',
            'nickname' => 'nickname',
            'password' => str_random(17)
        ])->assertDontSee(trans('setup.wizard.finish.title'));

        // Confirmation is not OK
        $this->post('/setup/finish', [
            'email' => 'a@b.c',
            'nickname' => 'nickname',
            'password' => '12345678',
            'password_confirmation' => '12345679'
        ])->assertDontSee(trans('setup.wizard.finish.title'));

        // Without `site_name` field
        $this->post('/setup/finish', [
            'email' => 'a@b.c',
            'nickname' => 'nickname',
            'password' => '12345678',
            'password_confirmation' => '12345678'
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
            ->with('migrate', ['--force' => true])
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
            'generate_random' => true
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
        $this->get('/setup/update')
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

        Artisan::shouldReceive('call')
            ->with('view:clear')
            ->andThrow(new Exception());
        config(['options.new_option' => 'value']);
        $this->post('/setup/update')->assertViewHas('tips');
        $this->assertEquals('value', option('new_option'));
        $this->assertEquals('3.1.1', option('version'));
        unlink(database_path("update_scripts/update-$current_version-to-100.0.0.php"));

        option(['version' => '3.0.0']);   // Fake old version
        $this->post('/setup/update');
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

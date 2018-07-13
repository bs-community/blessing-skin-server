<?php

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
        $type = get_db_type();

        if ($type === 'SQLite') {
            $server = get_db_config()['database'];
        } else {
            $config = get_db_config();
            $server = "{$config['username']}@{$config['host']}";
        }

        $this->get('/setup')->assertSee($type)->assertSee($server);
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

        // Without `password` field
        $this->post('/setup/finish', [
            'email' => 'a@b.c'
        ])->assertDontSee(trans('setup.wizard.finish.title'));

        // Password is too short
        $this->post('/setup/finish', [
            'email' => 'a@b.c',
            'password' => '1'
        ])->assertDontSee(trans('setup.wizard.finish.title'));

        // Password is too long
        $this->post('/setup/finish', [
            'email' => 'a@b.c',
            'password' => str_random(17)
        ])->assertDontSee(trans('setup.wizard.finish.title'));

        // Confirmation is not OK
        $this->post('/setup/finish', [
            'email' => 'a@b.c',
            'password' => '12345678',
            'password_confirmation' => '12345679'
        ])->assertDontSee(trans('setup.wizard.finish.title'));

        // Without `site_name` field
        $this->post('/setup/finish', [
            'email' => 'a@b.c',
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
            });
        $this->post('/setup/finish', [
            'email' => 'a@b.c',
            'password' => '12345678',
            'password_confirmation' => '12345678',
            'site_name' => 'bs',
            'generate_random' => true
        ])->assertSee(trans('setup.wizard.finish.title'))
            ->assertSee('a@b.c')
            ->assertSee('12345678');
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

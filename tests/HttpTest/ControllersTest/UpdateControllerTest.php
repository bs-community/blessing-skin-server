<?php

namespace Tests;

use App\Services\Unzip;
use Illuminate\Contracts\Console\Kernel as Artisan;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Finder\SplFileInfo;

class UpdateControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(factory(\App\Models\User::class)->states('superAdmin')->create());
    }

    public function testShowUpdatePage()
    {
        Http::fakeSequence()
            ->pushStatus(404)
            ->push($this->fakeUpdateInfo('8.9.3', ['spec' => 0]))
            ->push($this->fakeUpdateInfo('8.9.3', ['php' => '100.0.0']))
            ->push($this->fakeUpdateInfo('8.9.3'));

        // Can't connect to update source
        $this->get('/admin/update')->assertSee(config('app.version'));

        // Missing `spec` field
        $this->get('/admin/update')->assertSee(trans('admin.update.errors.spec'));

        // Low PHP version
        $this->get('/admin/update')->assertSee(trans('admin.update.errors.php', ['version' => '100.0.0']));

        // New version available
        $this->get('/admin/update')->assertSee(config('app.version'))->assertSee('8.9.3');
    }

    public function testDownload()
    {
        Http::fake([
            config('app.update_source') => Http::sequence()
                ->push($this->fakeUpdateInfo('1.2.3'))
                ->whenEmpty($this->fakeUpdateInfo('8.9.3')),
            'https://whatever.test/8.9.3/update.zip' => Http::sequence()
                ->pushStatus(404)
                ->pushStatus(200),
        ]);

        // Already up-to-date
        $this->postJson('/admin/update/download')
            ->assertJson([
                'code' => 1,
                'message' => trans('admin.update.info.up-to-date'),
            ]);

        // Download
        $this->postJson('/admin/update/download')->assertJson(['code' => 1]);
        $this->mock(Unzip::class, function ($mock) {
            $mock->shouldReceive('extract')->once()->andReturn();
        });
        $this->mock(\Illuminate\Filesystem\Filesystem::class, function ($mock) {
            $mock->shouldReceive('delete')->with(storage_path('options.php'))->once();
            $mock->shouldReceive('exists')->with(storage_path('install.lock'))->andReturn(true);
        });
        $this->postJson('/admin/update/download')
            ->assertJson(['code' => 0, 'message' => trans('admin.update.complete')]);
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
        $this->spy(Artisan::class, function ($spy) {
            $spy->shouldReceive('call')
                ->with('migrate', ['--force' => true])
                ->once();
            $spy->shouldReceive('call')->with('view:clear')->once();
        });
        config(['app.version' => '100.0.0']);

        $this->actingAs(factory(\App\Models\User::class)->states('superAdmin')->create())
            ->get('/setup/exec-update')
            ->assertViewIs('setup.updates.success');
        $this->assertEquals('100.0.0', option('version'));
    }

    protected function fakeUpdateInfo(string $version, $extra = [])
    {
        return array_merge([
            'spec' => 2,
            'php' => '7.2.5',
            'latest' => $version,
            'url' => "https://whatever.test/$version/update.zip",
        ], $extra);
    }
}

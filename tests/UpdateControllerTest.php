<?php

use org\bovigo\vfs;
use Illuminate\Support\Facades\File;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UpdateControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp()
    {
        parent::setUp();

        vfs\vfsStream::setup();

        return $this->actAs('admin');
    }

    /**
     * @param  string $version
     * @param  bool   $is_pre_release
     * @return string
     */
    protected function generateFakeUpdateInfo($version, $is_pre_release = false) {
        $time = \Carbon\Carbon::now();
        file_put_contents(vfs\vfsStream::url('root/update.json'), json_encode([
            'app_name' => 'blessing-skin-server',
            'latest_version' => $version,
            'update_time' => $time->getTimestamp(),
            'releases' => [
                $version => [
                    'version' => $version,
                    'pre_release' => $is_pre_release,
                    'release_time' => $time->getTimestamp(),
                    'release_note' => 'test',
                    'release_url' => storage_path('testing/update.zip')
                ]
            ]
        ]));

        return $time->toDateTimeString();
    }

    protected function generateFakeUpdateFile()
    {
        if (file_exists(storage_path('testing/update.zip'))) {
            unlink(storage_path('testing/update.zip'));
        }

        $zip = new ZipArchive();
        $zip->open(storage_path('testing/update.zip'), ZipArchive::CREATE);
        $zip->addEmptyDir('coverage');
        $zip->close();
    }

    public function testShowUpdatePage()
    {
        option(['update_source' => 'http://xxx.xx/']);
        $this->visit('/admin/update')
            ->see(trans('admin.update.errors.connection'))
            ->see(config('app.version'))
            ->uncheck('check_update')
            ->type(vfs\vfsStream::url('root/update.json'), 'update_source')
            ->press('submit_update');
        $this->assertFalse(option('check_update'));
        $this->assertEquals(
            vfs\vfsStream::url('root/update.json'),
            option('update_source')
        );

        $time = $this->generateFakeUpdateInfo('4.0.0');
        $this->visit('/admin/update')
            ->see(config('app.version'))
            ->see('4.0.0')
            ->see('test')
            ->see($time);

        file_put_contents(vfs\vfsStream::url('root/update.json'), json_encode([
            'latest_version' => '4.0.0'
        ]));
        $this->visit('/admin/update')
            ->see(trans('admin.update.info.pre-release'));
    }

    public function testCheckUpdates()
    {
        option(['update_source' => 'http://xxx.xx/']);

        // Source is unavailable
        $this->get('/admin/update/check')
            ->seeJson([
                'latest' => null,
                'available' => false
            ]);

        option(['update_source' => vfs\vfsStream::url('root/update.json')]);
        $this->generateFakeUpdateInfo('4.0.0');
        $this->get('/admin/update/check')
            ->seeJson([
                'latest' => '4.0.0',
                'available' => true
            ]);
    }

    public function testDownload()
    {
        option(['update_source' => vfs\vfsStream::url('root/update.json')]);
        $this->generateFakeUpdateInfo('0.1.0');
        $this->get('/admin/update/download');

        $this->generateFakeUpdateFile();

        // Prepare for downloading
        Storage::disk('root')->deleteDirectory('storage/update_cache');
        $this->generateFakeUpdateInfo('4.0.0');
        Storage::shouldReceive('disk')
            ->with('root')
            ->once()
            ->andReturnSelf();
        Storage::shouldReceive('makeDirectory')
            ->with('storage/update_cache')
            ->once()
            ->andReturn(false);
        $this->get('/admin/update/download?action=prepare-download')
            ->see(trans('admin.update.errors.write-permission'));

        mkdir(storage_path('update_cache'));
        $this->get('/admin/update/download?action=prepare-download')
            ->seeJson([
                'release_url' => storage_path('testing/update.zip'),
                'file_size' => filesize(storage_path('testing/update.zip'))
            ])
            ->assertSessionHas('tmp_path');

        // Start downloading
        $this->flushSession();
        $this->actAs('admin')
            ->get('/admin/update/download?action=start-download')
            ->see('No temp path is set.');

        unlink(storage_path('testing/update.zip'));
        $this->withSession(['tmp_path' => storage_path('update_cache/update.zip')])
            ->get('/admin/update/download?action=start-download')
            ->see(trans('admin.update.errors.prefix'));

        $this->generateFakeUpdateFile();
        $this->get('/admin/update/download?action=start-download')
            ->seeJson([
                'tmp_path' => storage_path('update_cache/update.zip')
            ]);
        $this->assertFileExists(storage_path('update_cache/update.zip'));

        // Get file size
        $this->flushSession();
        $this->actAs('admin')
            ->get('/admin/update/download?action=get-file-size')
            ->see('No temp path is set.');

        $this->withSession(['tmp_path' => storage_path('update_cache/update.zip')])
            ->get('/admin/update/download?action=get-file-size')
            ->seeJson([
                'size' => filesize(storage_path('testing/update.zip'))
            ]);

        // Extract
        $this->withSession(['tmp_path' => storage_path('update_cache/update')])
            ->get('/admin/update/download?action=extract')
            ->see('No file available');

        file_put_contents(storage_path('update_cache/update.zip'), 'text');
        $this->withSession(['tmp_path' => storage_path('update_cache/update.zip')])
            ->get('/admin/update/download?action=extract')
            ->see(trans('admin.update.errors.unzip'));

        copy(
            storage_path('testing/update.zip'),
            storage_path('update_cache/update.zip')
        );
        $this->get('/admin/update/download?action=extract')
            ->see(trans('admin.update.complete'));


        mkdir(storage_path('update_cache'));
        copy(
            storage_path('testing/update.zip'),
            storage_path('update_cache/update.zip')
        );
        File::shouldReceive('copyDirectory')
            ->with(storage_path('update_cache/4.0.0/vendor'), base_path('vendor'))
            ->andThrow(new \Exception);
        File::shouldReceive('deleteDirectory')
            ->with(storage_path('update_cache/4.0.0/vendor'));
        $this->get('/admin/update/download?action=extract');


        File::shouldReceive('copyDirectory')
            ->with(storage_path('update_cache/4.0.0'), base_path())
            ->andThrow(new Exception);
        File::shouldReceive('deleteDirectory')
            ->with(storage_path('update_cache'));
        File::shouldReceive('deleteDirectory')
            ->with(storage_path('update_cache'));
        $this->get('/admin/update/download?action=extract')
            ->see(trans('admin.update.errors.overwrite'));

        // Invalid action
        $this->get('/admin/update/download?action=no')
            ->seeJson([
                'errno' => 1,
                'msg' => trans('general.illegal-parameters')
            ]);
    }
}

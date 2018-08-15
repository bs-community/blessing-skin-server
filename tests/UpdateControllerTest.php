<?php

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\File;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UpdateControllerTest extends TestCase
{
    use MocksGuzzleClient;
    use DatabaseTransactions;

    protected function setUp()
    {
        parent::setUp();
        return $this->actAs('superAdmin');
    }

    public function testShowUpdatePage()
    {
        $this->setupGuzzleClientMock();

        // Can't connect to update source
        $this->appendToGuzzleQueue([
            new RequestException('Connection Error', new Request('GET', 'whatever')),
            new RequestException('Connection Error', new Request('GET', 'whatever')),
        ]);
        $this->visit('/admin/update')
            ->see(trans('admin.update.errors.connection', ['error' => 'Connection Error']))
            ->see(config('app.version'));

        // New version available
        $time = time();
        $this->appendToGuzzleQueue(200, [], $this->generateFakeUpdateInfo('8.9.3', false, $time));
        $this->visit('/admin/update')
            ->see(config('app.version'))
            ->see('8.9.3')
            ->see('test')
            ->see(Carbon\Carbon::createFromTimestamp($time)->toDateTimeString());

        // Now using pre-release version
        $this->appendToGuzzleQueue(200, [], $this->generateFakeUpdateInfo('0.0.1', false, $time));
        $this->visit('/admin/update')
            ->see(trans('admin.update.info.pre-release'));
    }

    public function testCheckUpdates()
    {
        $this->setupGuzzleClientMock();

        // Update source is unavailable
        $this->appendToGuzzleQueue([
            new RequestException('Connection Error', new Request('GET', 'whatever')),
            new RequestException('Connection Error', new Request('GET', 'whatever')),
        ]);
        $this->get('/admin/update/check')
            ->seeJson([
                'latest' => null,
                'available' => false
            ]);

        // New version available
        $this->appendToGuzzleQueue(200, [], $this->generateFakeUpdateInfo('8.9.3', false, time()));
        $this->get('/admin/update/check')
            ->seeJson([
                'latest' => '8.9.3',
                'available' => true
            ]);
    }

    public function testDownload()
    {
        $this->setupGuzzleClientMock();

        // Already up-to-date
        $this->appendToGuzzleQueue(200, [], $this->generateFakeUpdateInfo(config('app.version')));
        $this->get('/admin/update/download')->dontSee(trans('general.illegal-parameters'));

        // Lack write permission
        File::deleteDirectory(storage_path('update_cache'));
        Storage::shouldReceive('disk')
            ->with('root')
            ->once()
            ->andReturnSelf();
        Storage::shouldReceive('makeDirectory')
            ->with('storage/update_cache')
            ->once()
            ->andReturn(false);
        $this->withNewVersionAvailable()
            ->get('/admin/update/download?action=prepare-download')
            ->see(trans('admin.update.errors.write-permission'));

        // Prepare for downloading
        mkdir(storage_path('update_cache'));
        $this->withNewVersionAvailable()
            ->get('/admin/update/download?action=prepare-download')
            ->seeJson([
                'release_url' => 'https://whatever.test/8.9.3/update.zip',
            ])
            ->seeInCache('tmp_path')
            ->assertCacheMissing('download-progress');

        // Start downloading
        $this->flushCache();
        $this->withNewVersionAvailable()
            ->get('/admin/update/download?action=start-download')
            ->see('No temp path available, please try again.');

        // Can't download update package
        $this->appendToGuzzleQueue([
            new Response(200, [], $this->generateFakeUpdateInfo('8.9.3')),
            new RequestException('Connection Error', new Request('GET', 'whatever')),
        ]);
        $this->withCache(['tmp_path' => storage_path('update_cache/update.zip')])
            ->get('/admin/update/download?action=start-download')
            ->see(trans('admin.update.errors.prefix'));
        $this->assertFileNotExists(storage_path('update_cache/update.zip'));

        // Download update package
        $fakeUpdatePackage = $this->generateFakeUpdateFile();
        $this->appendToGuzzleQueue([
            new Response(200, [], $this->generateFakeUpdateInfo('8.9.3')),
            new Response(200, [], fopen($fakeUpdatePackage, 'r')),
        ]);
        $this->withCache(['tmp_path' => storage_path('update_cache/update.zip')])
            ->get('/admin/update/download?action=start-download')
            ->seeJson([
                'tmp_path' => storage_path('update_cache/update.zip')
            ]);
        $this->assertFileExists(storage_path('update_cache/update.zip'));

        // No download progress available
        $this->flushCache();
        $this->withNewVersionAvailable()
            ->get('/admin/update/download?action=get-progress')
            ->see('[]');

        // Get download progress
        $this->withNewVersionAvailable()
            ->withCache(['download-progress' => ['total' => 514, 'downloaded' => 114]])
            ->get('/admin/update/download?action=get-progress')
            ->seeJson([
                'total' => 514,
                'downloaded' => 114
            ]);

        // No such zip archive
        $this->withNewVersionAvailable()
            ->withCache(['tmp_path' => storage_path('update_cache/nope.zip')])
            ->get('/admin/update/download?action=extract')
            ->see('No file available');

        // Can't extract zip archive
        file_put_contents(storage_path('update_cache/update.zip'), 'text');
        $this->withNewVersionAvailable()
            ->withCache(['tmp_path' => storage_path('update_cache/update.zip')])
            ->get('/admin/update/download?action=extract')
            ->see(trans('admin.update.errors.unzip'));

        // Extract
        copy(storage_path('testing/update.zip'), storage_path('update_cache/update.zip'));
        $this->withNewVersionAvailable()
            ->get('/admin/update/download?action=extract')
            ->see(trans('admin.update.complete'));

        // Can't overwrite vendor directory, skip
        mkdir(storage_path('update_cache'));
        copy(storage_path('testing/update.zip'), storage_path('update_cache/update.zip'));
        File::shouldReceive('copyDirectory')
            ->with(storage_path('update_cache/8.9.3/vendor'), base_path('vendor'))
            ->andThrow(new Exception);
        File::shouldReceive('deleteDirectory')
            ->with(storage_path('update_cache/8.9.3/vendor'));
        $this->withNewVersionAvailable()
            ->get('/admin/update/download?action=extract');

        // Can't apply update package
        File::shouldReceive('copyDirectory')
            ->with(storage_path('update_cache/8.9.3'), base_path())
            ->andThrow(new Exception);
        File::shouldReceive('deleteDirectory')
            ->with(storage_path('update_cache'));
        File::shouldReceive('deleteDirectory')
            ->with(storage_path('update_cache'));
        $this->withNewVersionAvailable()
            ->get('/admin/update/download?action=extract')
            ->see(trans('admin.update.errors.overwrite'));

        // Invalid action
        $this->withNewVersionAvailable()
            ->get('/admin/update/download?action=no')
            ->seeJson([
                'errno' => 1,
                'msg' => trans('general.illegal-parameters')
            ]);
    }

    protected function withNewVersionAvailable()
    {
        $this->appendToGuzzleQueue(200, [], $this->generateFakeUpdateInfo('8.9.3'));
        return $this;
    }

    protected function generateFakeUpdateInfo($version, $preview = false, $time = null)
    {
        $time = $time ?: time();

        return json_encode([
            'app_name' => 'blessing-skin-server',
            'latest_version' => $version,
            'update_time' => $time,
            'releases' => [
                $version => [
                    'version' => $version,
                    'pre_release' => $preview,
                    'release_time' => $time,
                    'release_note' => 'test',
                    'release_url' => "https://whatever.test/$version/update.zip"
                ]
            ]
        ]);
    }

    protected function generateFakeUpdateFile()
    {
        $zipPath = storage_path('testing/update.zip');

        if (file_exists($zipPath)) {
            unlink($zipPath);
        }

        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE);
        $zip->addEmptyDir('coverage');
        $zip->close();

        return $zipPath;
    }
}

<?php

namespace Tests;

use Cache;
use Exception;
use Carbon\Carbon;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use App\Services\PackageManager;
use Illuminate\Support\Facades\File;
use Tests\Concerns\MocksGuzzleClient;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UpdateControllerTest extends TestCase
{
    use DatabaseTransactions;
    use MocksGuzzleClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAs('superAdmin');
    }

    public function testShowUpdatePage()
    {
        $this->setupGuzzleClientMock();

        // Can't connect to update source
        $this->appendToGuzzleQueue([
            new RequestException('Connection Error', new Request('GET', 'whatever')),
            new RequestException('Connection Error', new Request('GET', 'whatever')),
        ]);
        $this->get('/admin/update')->assertSee(config('app.version'));

        // New version available
        $time = time();
        $this->appendToGuzzleQueue(200, [], $this->generateFakeUpdateInfo('8.9.3', false, $time));
        $this->get('/admin/update')->assertSee(config('app.version'))->assertSee('8.9.3');

        // Now using pre-release version
        $this->appendToGuzzleQueue(200, [], $this->generateFakeUpdateInfo('0.0.1', false, $time));
        $this->get('/admin/update');
    }

    public function testCheckUpdates()
    {
        $this->setupGuzzleClientMock();

        // Update source is unavailable
        $this->appendToGuzzleQueue([
            new RequestException('Connection Error', new Request('GET', 'whatever')),
            new RequestException('Connection Error', new Request('GET', 'whatever')),
        ]);
        $this->getJson('/admin/update/check')
            ->assertJson([
                'latest' => null,
                'available' => false,
            ]);

        // New version available
        $this->appendToGuzzleQueue(200, [], $this->generateFakeUpdateInfo('8.9.3', false, time()));
        $this->getJson('/admin/update/check')
            ->assertJson([
                'latest' => '8.9.3',
                'available' => true,
            ]);
    }

    public function testDownload()
    {
        $this->setupGuzzleClientMock();

        // Already up-to-date
        $this->getJson('/admin/update/download')
            ->assertDontSee(trans('general.illegal-parameters'));

        // Download
        $this->appendToGuzzleQueue([
            new Response(200, [], $this->generateFakeUpdateInfo('8.9.3')),
            new Response(200, [], $this->generateFakeUpdateInfo('8.9.3')),
        ]);
        app()->instance(PackageManager::class, new Concerns\FakePackageManager(null, true));
        $this->getJson('/admin/update/download?action=download')
            ->assertJson(['errno' => 1]);
        app()->bind(PackageManager::class, Concerns\FakePackageManager::class);
        $this->getJson('/admin/update/download?action=download')
            ->assertJson(['errno' => 0, 'msg' => trans('admin.update.complete')]);

        // Get download progress
        $this->getJson('/admin/update/download?action=progress')
            ->assertSee('0');

        // Invalid action
        $this->appendToGuzzleQueue(200, [], $this->generateFakeUpdateInfo('8.9.3'));
        $this->getJson('/admin/update/download?action=no')
            ->assertJson([
                'errno' => 1,
                'msg' => trans('general.illegal-parameters'),
            ]);
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
                    'release_url' => "https://whatever.test/$version/update.zip",
                ],
            ],
        ]);
    }
}

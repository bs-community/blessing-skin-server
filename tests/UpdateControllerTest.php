<?php

namespace Tests;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use App\Services\PackageManager;
use Tests\Concerns\MocksGuzzleClient;
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
        ]);
        $this->get('/admin/update')->assertSee(config('app.version'));

        // Missing `spec` field
        $this->appendToGuzzleQueue([
            new Response(200, [], json_encode(['latest' => '8.9.3', 'url' => ''])),
        ]);
        $this->get('/admin/update')->assertSee(trans('admin.update.spec'));

        // New version available
        $this->appendToGuzzleQueue([
            new Response(200, [], $this->mockFakeUpdateInfo('8.9.3')),
        ]);
        $this->get('/admin/update')->assertSee(config('app.version'))->assertSee('8.9.3');
    }

    public function testCheckUpdates()
    {
        $this->setupGuzzleClientMock();

        // Update source is unavailable
        $this->appendToGuzzleQueue([
            new RequestException('Connection Error', new Request('GET', 'whatever')),
        ]);
        $this->getJson('/admin/update/check')->assertJson(['available' => false]);

        // New version available
        $this->appendToGuzzleQueue(200, [], $this->mockFakeUpdateInfo('8.9.3'));
        $this->getJson('/admin/update/check')->assertJson(['available' => true]);
    }

    public function testDownload()
    {
        $this->setupGuzzleClientMock();

        // Already up-to-date
        $this->getJson('/admin/update/download')
            ->assertDontSee(trans('general.illegal-parameters'));

        // Download
        $this->appendToGuzzleQueue([
            new Response(200, [], $this->mockFakeUpdateInfo('8.9.3')),
            new Response(200, [], $this->mockFakeUpdateInfo('8.9.3')),
        ]);
        app()->instance(PackageManager::class, new Concerns\FakePackageManager(null, true));
        $this->getJson('/admin/update/download?action=download')
            ->assertJson(['code' => 1]);
        app()->bind(PackageManager::class, Concerns\FakePackageManager::class);
        $this->getJson('/admin/update/download?action=download')
            ->assertJson(['code' => 0, 'message' => trans('admin.update.complete')]);

        // Get download progress
        $this->getJson('/admin/update/download?action=progress')
            ->assertSee('0');

        // Invalid action
        $this->appendToGuzzleQueue(200, [], $this->mockFakeUpdateInfo('8.9.3'));
        $this->getJson('/admin/update/download?action=no')
            ->assertJson([
                'code' => 1,
                'message' => trans('general.illegal-parameters'),
            ]);
    }

    protected function mockFakeUpdateInfo($version)
    {
        return json_encode([
            'spec' => 1,
            'latest' => $version,
            'url' => "https://whatever.test/$version/update.zip",
        ]);
    }
}

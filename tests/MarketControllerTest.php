<?php

namespace Tests;

use App\Services\Plugin;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use App\Services\PluginManager;
use App\Services\PackageManager;
use Tests\Concerns\MocksGuzzleClient;
use GuzzleHttp\Exception\RequestException;

class MarketControllerTest extends TestCase
{
    use MocksGuzzleClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAs('superAdmin');
    }

    public function testDownload()
    {
        $this->setupGuzzleClientMock();

        // Try to download a non-existent plugin
        $this->appendToGuzzleQueue(200, [], json_encode([
            'version' => 1,
            'packages' => [],
        ]));
        $this->postJson('/admin/plugins/market/download', [
            'name' => 'non-existent-plugin',
        ])->assertJson([
            'code' => 1,
            'message' => trans('admin.plugins.market.non-existent', ['plugin' => 'non-existent-plugin']),
        ]);

        // Download
        $fakeRegistry = json_encode(['packages' => [
            [
                'name' => 'fake',
                'version' => '0.0.0',
                'dist' => ['url' => 'http://nowhere.test/', 'shasum' => 'deadbeef'],
            ],
        ]]);
        $this->appendToGuzzleQueue([new Response(200, [], $fakeRegistry)]);
        $this->mock(PackageManager::class, function ($mock) {
            $mock->shouldReceive('download')
                ->withArgs(['http://nowhere.test/', storage_path('packages/fake_0.0.0.zip'), 'deadbeef'])
                ->once()
                ->andThrow(new \Exception());
        });
        $this->postJson('/admin/plugins/market/download', [
            'name' => 'fake',
        ])->assertJson(['code' => 1]);

        $this->appendToGuzzleQueue([new Response(200, [], $fakeRegistry)]);
        $this->mock(PackageManager::class, function ($mock) {
            $mock->shouldReceive('download')
                ->withArgs(['http://nowhere.test/', storage_path('packages/fake_0.0.0.zip'), 'deadbeef'])
                ->once()
                ->andReturnSelf();
            $mock->shouldReceive('extract')
                ->with(base_path('plugins'))
                ->once();
        });
        $this->postJson('/admin/plugins/market/download', [
            'name' => 'fake',
        ])->assertJson(['code' => 0, 'message' => trans('admin.plugins.market.install-success')]);
    }

    public function testCheckUpdates()
    {
        $this->setupGuzzleClientMock();

        $fakeRegistry = json_encode(['packages' => [
            [
                'name' => 'fake',
                'version' => '0.0.1',
                'dist' => ['url' => 'http://nowhere.test/', 'shasum' => 'deadbeef'],
            ],
        ]]);

        // Not installed
        $this->appendToGuzzleQueue(200, [], $fakeRegistry);
        $this->getJson('/admin/plugins/market/check')
            ->assertJson([
                'available' => false,
                'plugins' => [],
            ]);

        $this->mock(PluginManager::class, function ($mock) {
            $mock->shouldReceive('get')
                ->with('fake')
                ->twice()
                ->andReturn(new Plugin('', ['name' => 'fake', 'version' => '0.0.1']));
        });
        // Plugin up-to-date
        $this->appendToGuzzleQueue(200, [], $fakeRegistry);
        $this->getJson('/admin/plugins/market/check')
            ->assertJson([
                'available' => false,
                'plugins' => [],
            ]);

        // New version available
        $fakeRegistry = json_encode(['packages' => [
            [
                'name' => 'fake',
                'version' => '2.3.3',
                'dist' => ['url' => 'http://nowhere.test/', 'shasum' => 'deadbeef'],
            ],
        ]]);
        $this->appendToGuzzleQueue(200, [], $fakeRegistry);
        $this->getJson('/admin/plugins/market/check')
            ->assertJson([
                'available' => true,
                'plugins' => [[
                    'name' => 'fake',
                ]],
            ]);
    }

    public function testMarketData()
    {
        $this->setupGuzzleClientMock([
            new RequestException('Connection Error', new Request('POST', 'whatever')),
            new Response(200, [], json_encode(['version' => 1, 'packages' => [
                [
                    'name' => 'fake1',
                    'title' => 'Fake',
                    'version' => '1.0.0',
                    'description' => '',
                    'author' => '',
                    'dist' => [],
                    'require' => [],
                ],
                [
                    'name' => 'fake2',
                    'title' => 'Fake',
                    'version' => '0.0.0',
                    'description' => '',
                    'author' => '',
                    'dist' => [],
                    'require' => [],
                ],
            ]])),
            new Response(200, [], json_encode(['version' => 0])),
        ]);

        // Expected an exception, but unable to be asserted.
        $this->getJson('/admin/plugins/market-data');

        $this->mock(PluginManager::class, function ($mock) {
            $mock->shouldReceive('get')
                ->with('fake1')
                ->once()
                ->andReturn(new Plugin('', ['name' => 'fake1', 'version' => '0.0.1']));
            $mock->shouldReceive('get')
                ->with('fake2')
                ->once()
                ->andReturn(null);
            $mock->shouldReceive('getUnsatisfied')->twice();
        });
        $this->getJson('/admin/plugins/market-data')
            ->assertJsonStructure([
                [
                    'name',
                    'title',
                    'version',
                    'installed',
                    'description',
                    'author',
                    'dist',
                    'dependencies',
                ],
            ]);

        $this->getJson('/admin/plugins/market-data')
            ->assertJson(['message' => 'Only version 1 of market registry is accepted.']);
    }
}

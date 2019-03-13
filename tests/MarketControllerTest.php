<?php

namespace Tests;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\File;
use Tests\Concerns\MocksGuzzleClient;
use Tests\Concerns\GeneratesFakePlugins;
use GuzzleHttp\Exception\RequestException;

class MarketControllerTest extends TestCase
{
    use MocksGuzzleClient;
    use GeneratesFakePlugins;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAs('superAdmin');
    }

    public function testDownload()
    {
        $this->setupGuzzleClientMock();

        // Try to download a non-existent plugin
        $this->appendToGuzzleQueue(200, [], $this->generateFakePluginsRegistry());
        $this->postJson('/admin/plugins/market/download', [
            'name' => 'non-existent-plugin',
        ])->assertJson([
            'errno' => 1,
            'msg' => trans('admin.plugins.market.non-existent', ['plugin' => 'non-existent-plugin']),
        ]);

        // Can't download due to connection error
        $this->appendToGuzzleQueue([
            new Response(200, [], $this->generateFakePluginsRegistry('fake-test-download', '0.0.1')),
            new RequestException('Connection Error', new Request('GET', 'whatever')),
        ]);
        $this->postJson('/admin/plugins/market/download', [
            'name' => 'fake-test-download',
        ])->assertJson([
            'errno' => 2,
            'msg' => trans('admin.plugins.market.download-failed', ['error' => 'Connection Error']),
        ]);

        // Downloaded plugin archive was tampered
        $fakeArchive = $this->generateFakePluginArchive(['name' => 'fake-test-download', 'version' => '0.0.1']);
        $this->appendToGuzzleQueue([
            new Response(200, [], $this->generateFakePluginsRegistry('fake-test-download', '0.0.1')),
            new Response(200, [], fopen($fakeArchive, 'r')),
        ]);
        $this->postJson('/admin/plugins/market/download', [
            'name' => 'fake-test-download',
        ])->assertJson([
            'errno' => 3,
            'msg' => trans('admin.plugins.market.shasum-failed'),
        ]);

        // Download and extract plugin
        $shasum = sha1_file($fakeArchive);
        $this->appendToGuzzleQueue([
            new Response(200, [], $this->generateFakePluginsRegistry([
                [
                    'name' => 'fake-test-download',
                    'version' => '0.0.1',
                    'dist' => [
                        'url' => 'whatever',
                        'shasum' => $shasum,
                    ],
                ],
            ])),
            new Response(200, [], fopen($fakeArchive, 'r')),
        ]);
        $this->postJson('/admin/plugins/market/download', [
            'name' => 'fake-test-download',
        ])->assertJson([
            'errno' => 0,
            'msg' => trans('admin.plugins.market.install-success'),
        ]);
        $this->assertTrue(is_dir(config('plugins.directory').DIRECTORY_SEPARATOR.'fake-test-download'));
        $this->assertTrue(
            empty(glob(config('plugins.directory').DIRECTORY_SEPARATOR.'fake-test-download_*.zip'))
        );

        // Broken archive
        file_put_contents($fakeArchive, 'broken');
        $shasum = sha1_file($fakeArchive);
        $this->appendToGuzzleQueue([
            new Response(200, [], $this->generateFakePluginsRegistry([
                [
                    'name' => 'fake-test-download',
                    'version' => '0.0.1',
                    'dist' => [
                        'url' => 'whatever',
                        'shasum' => $shasum,
                    ],
                ],
            ])),
            new Response(200, [], fopen($fakeArchive, 'r')),
        ]);
        $this->postJson('/admin/plugins/market/download', [
            'name' => 'fake-test-download',
        ])->assertJson([
            'errno' => 4,
            'msg' => trans('admin.plugins.market.unzip-failed', ['error' => 19]),
        ]);
    }

    public function testCheckUpdates()
    {
        $this->setupGuzzleClientMock();

        // Not installed
        $this->appendToGuzzleQueue(200, [], $this->generateFakePluginsRegistry('fake-test-update', '0.0.1'));
        $this->getJson('/admin/plugins/market/check')
            ->assertJson([
                'available' => false,
                'plugins' => [],
            ]);

        // Generate fake plugin and refresh plugin manager
        $this->generateFakePlugin(['name' => 'fake-test-update', 'version' => '0.0.1']);
        $this->app->singleton('plugins', \App\Services\PluginManager::class);

        // Plugin up-to-date
        $this->appendToGuzzleQueue(200, [], $this->generateFakePluginsRegistry('fake-test-update', '0.0.1'));
        $this->getJson('/admin/plugins/market/check')
            ->assertJson([
                'available' => false,
                'plugins' => [],
            ]);

        // New version available
        $this->appendToGuzzleQueue(200, [], $this->generateFakePluginsRegistry('fake-test-update', '2.3.3'));
        $this->getJson('/admin/plugins/market/check')
            ->assertJson([
                'available' => true,
                'plugins' => [[
                    'name' => 'fake-test-update',
                ]],
            ]);
    }

    public function testMarketData()
    {
        $registry = $this->generateFakePluginsRegistry();
        $package = json_decode($registry, true)['packages'][0];
        $this->generateFakePlugin($package);
        $this->setupGuzzleClientMock([
            new RequestException('Connection Error', new Request('POST', 'whatever')),
            new Response(200, [], $registry),
        ]);

        // Expected an exception, but unable to be asserted.
        $this->getJson('/admin/plugins/market-data');

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

        File::deleteDirectory(config('plugins.directory').DIRECTORY_SEPARATOR.$package['name']);
    }

    protected function tearDown(): void
    {
        // Clean fake plugins
        File::deleteDirectory(config('plugins.directory').DIRECTORY_SEPARATOR.'fake-test-download');
        File::deleteDirectory(config('plugins.directory').DIRECTORY_SEPARATOR.'fake-test-update');
        File::delete(config('plugins.directory').DIRECTORY_SEPARATOR.'whatever');

        parent::tearDown();
    }
}

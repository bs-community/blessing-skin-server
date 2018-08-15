<?php

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;

class MarketControllerTest extends TestCase
{
    use GenerateFakePlugins;

    protected function setUp()
    {
        parent::setUp();
        return $this->actAs('superAdmin');
    }

    public function testShowMarket()
    {
        $this->visit('/admin/plugins/market')
            ->see(trans('general.plugin-market'));
    }

    public function testDownload()
    {
        $this->setupGuzzleClientMock();

        // Try to download a non-existent plugin
        $this->appendToGuzzleQueue(200, [], $this->generateFakePluginsRegistry());
        $this->post('/admin/plugins/market/download', [
            'name' => 'non-existent-plugin'
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('admin.plugins.market.non-existent', ['plugin' => 'non-existent-plugin'])
        ]);

        // Can't download due to connection error
        $this->appendToGuzzleQueue([
            new Response(200, [], $this->generateFakePluginsRegistry('fake-test-download', '0.0.1')),
            new RequestException('Connection Error', new Request('GET', 'whatever')),
        ]);
        $this->post('/admin/plugins/market/download', [
            'name' => 'fake-test-download'
        ])->seeJson([
            'errno' => 2,
            'msg' => trans('admin.plugins.market.download-failed', ['error' => 'Connection Error'])
        ]);

        // Downloaded plugin archive was tampered
        $fakeArchive = $this->generateFakePluginArchive(['name' => 'fake-test-download', 'version' => '0.0.1']);
        $this->appendToGuzzleQueue([
            new Response(200, [], $this->generateFakePluginsRegistry('fake-test-download', '0.0.1')),
            new Response(200, [], fopen($fakeArchive, 'r')),
        ]);
        $this->post('/admin/plugins/market/download', [
            'name' => 'fake-test-download'
        ])->seeJson([
            'errno' => 3,
            'msg' => trans('admin.plugins.market.shasum-failed')
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
                        'shasum' => $shasum
                    ]
                ]
            ])),
            new Response(200, [], fopen($fakeArchive, 'r')),
        ]);
        $this->post('/admin/plugins/market/download', [
            'name' => 'fake-test-download'
        ])->seeJson([
            'errno' => 0,
            'msg' => trans('admin.plugins.market.install-success')
        ]);
        $this->assertTrue(is_dir(base_path('plugins/fake-test-download')));
        $this->assertTrue(empty(glob(base_path('plugins/fake-test-download_*.zip'))));
    }

    public function testCheckUpdates()
    {
        $this->setupGuzzleClientMock();

        // Not installed
        $this->appendToGuzzleQueue(200, [], $this->generateFakePluginsRegistry('fake-test-update', '0.0.1'));
        $this->get('/admin/plugins/market/check')
            ->seeJson([
                'available' => false,
                'plugins' => []
            ]);

        // Generate fake plugin and refresh plugin manager
        $this->generateFakePlugin(['name' => 'fake-test-update', 'version' => '0.0.1']);
        $this->app->singleton('plugins', App\Services\PluginManager::class);

        // Plugin up-to-date
        $this->appendToGuzzleQueue(200, [], $this->generateFakePluginsRegistry('fake-test-update', '0.0.1'));
        $this->get('/admin/plugins/market/check')
            ->seeJson([
                'available' => false,
                'plugins' => []
            ]);

        // New version available
        $this->appendToGuzzleQueue(200, [], $this->generateFakePluginsRegistry('fake-test-update', '2.3.3'));
        $this->get('/admin/plugins/market/check')
            ->seeJsonSubset([
                'available' => true,
                'plugins' => [[
                    'name' => 'fake-test-update'
                ]]
            ]);
    }

    public function testGetMarketData()
    {
        $this->setupGuzzleClientMock([
            new RequestException('Connection Error', new Request('POST', 'whatever')),
            new Response(200, [], $this->generateFakePluginsRegistry()),
        ]);

        $this->expectException(Exception::class)->post('/admin/plugins/market-data');

        $this->post('/admin/plugins/market-data')
            ->seeJsonStructure([
                'data' => [[
                    'name',
                    'title',
                    'version',
                    'installed',
                    'description',
                    'author',
                    'dist',
                    'dependencies'
                ]]
            ]);
    }

    protected function tearDown()
    {
        // Clean fake plugins
        File::deleteDirectory(base_path('plugins/fake-test-download'));
        File::deleteDirectory(base_path('plugins/fake-test-update'));

        parent::tearDown();
    }
}

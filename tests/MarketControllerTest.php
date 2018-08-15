<?php

class MarketControllerTest extends TestCase
{
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
        // Try to download a non-existent plugin
        $this->post('/admin/plugins/market/download', [
            'name' => 'non-existent-plugin'
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('admin.plugins.market.non-existent', ['plugin' => 'non-existent-plugin'])
        ]);

        // Download and extract plugin
        $this->post('/admin/plugins/market/download', [
            'name' => 'hello-dolly'
        ])->seeJson([
            'errno' => 0,
            'msg' => trans('admin.plugins.market.install-success')
        ]);
        $this->assertTrue(is_dir(base_path('plugins/hello-dolly')));
        $this->assertTrue(empty(glob(base_path('plugins/hello-dolly_*.zip'))));
    }

    public function testCheckUpdates()
    {
        $plugin_dir = base_path('plugins/hello-dolly');

        if (! is_dir($plugin_dir)) {
            mkdir($plugin_dir);
        }

        $this->get('/admin/plugins/market/check')
            ->seeJson([
                'available' => false,
                'plugins' => []
            ]);

        file_put_contents("$plugin_dir/package.json", json_encode([
            'name' => 'hello-dolly',
            'version' => '0.0.1',
            'title' => '',
            'description' => '',
            'author' => '',
            'url' => '',
            'namespace' => ''
        ]));

        // Refresh plugin manager
        $this->app->singleton('plugins', App\Services\PluginManager::class);
        $this->get('/admin/plugins/market/check')
            ->seeJsonSubset([
                'available' => true,
                'plugins' => [[
                    'name' => 'hello-dolly'
                ]]
            ]);
    }

    public function testGetMarketData()
    {
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

        // Get plugins info without an valid certificate
        config(['secure.certificates' => '']);
        $this->expectException(Exception::class)->post('/admin/plugins/market-data');
    }
}

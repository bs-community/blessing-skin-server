<?php

class MarketControllerTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        return $this->actAs('admin');
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
        $this->assertFileNotExists(base_path('plugins/hello-dolly_v1.0.0.zip'));
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
                    'enabled',
                    'dependencies'
                ]]
            ]);

        // Get plugins info without an valid certificate
        config(['secure.certificates' => '']);
        $this->setExpectedException('Error')->post('/admin/plugins/market-data');
    }
}

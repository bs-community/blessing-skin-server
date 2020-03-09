<?php

namespace Tests;

use App\Services\Plugin;
use App\Services\PluginManager;
use App\Services\Unzip;
use Illuminate\Support\Facades\Http;

class MarketControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(factory(\App\Models\User::class)->states('superAdmin')->create());
    }

    public function testDownload()
    {
        Http::fake([
            config('plugins.registry') => Http::sequence()
                ->push(['version' => 1, 'packages' => []])
                ->push([
                    'version' => 1,
                    'packages' => [
                        [
                            'name' => 'fake',
                            'version' => '0.0.0',
                            'require' => ['a' => '^4.0.0'],
                        ],
                    ],
                ])
                ->whenEmpty([
                    'version' => 1,
                    'packages' => [
                        [
                            'name' => 'fake',
                            'version' => '0.0.0',
                            'dist' => [
                                'url' => 'http://nowhere.test/',
                                'shasum' => 'deadbeef',
                            ],
                        ],
                    ],
                ]),
            'http://nowhere.test/' => Http::sequence()
                ->pushStatus(404)
                ->pushStatus(200),
        ]);

        $this->postJson('/admin/plugins/market/download', ['name' => 'nope'])
            ->assertJson([
                'code' => 1,
                'message' => trans('admin.plugins.market.non-existent', ['plugin' => 'nope']),
            ]);

        // Unresolved plugin.
        $this->postJson('/admin/plugins/market/download', ['name' => 'fake'])
            ->assertJson([
                'message' => trans('admin.plugins.market.unresolved'),
                'code' => 1,
                'data' => [
                    'reason' => [
                        trans('admin.plugins.operations.unsatisfied.disabled', ['name' => 'a']),
                    ],
                ],
            ]);

        // Download
        $this->postJson('/admin/plugins/market/download', ['name' => 'fake'])
            ->assertJson(['code' => 1]);

        $this->mock(Unzip::class, function ($mock) {
            $mock->shouldReceive('extract')->once();
        });
        $this->postJson('/admin/plugins/market/download', ['name' => 'fake'])
            ->assertJson([
                'code' => 0,
                'message' => trans('admin.plugins.market.install-success'),
            ]);
    }

    public function testMarketData()
    {
        Http::fakeSequence()
            ->pushStatus(404)
            ->push([
                'version' => 1,
                'packages' => [
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
                ],
            ]);

        $this->getJson('/admin/plugins/market/list')->assertStatus(500);

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
        $this->getJson('/admin/plugins/market/list')
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
    }
}

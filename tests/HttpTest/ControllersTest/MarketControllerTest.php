<?php

namespace Tests;

use App\Models\User;
use App\Services\Plugin;
use App\Services\PluginManager;
use App\Services\Unzip;
use Illuminate\Support\Facades\Http;

class MarketControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superAdmin()->create());
    }

    public function testDownload()
    {
        $registryUrl = str_replace('{lang}', 'en', config('plugins.registry'));
        Http::fake([
            $registryUrl => Http::sequence()
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
        $registry = [
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
        ];

        Http::fakeSequence()
            ->pushStatus(404)
            ->push($registry)
            ->push($registry);

        $this->getJson('/admin/plugins/market/list')->assertStatus(500);

        $this->mock(PluginManager::class, function ($mock) {
            $mock->shouldReceive('get')
                ->with('fake1')
                ->atLeast()
                ->once()
                ->andReturn(new Plugin('', ['name' => 'fake1', 'version' => '0.0.1']));
            $mock->shouldReceive('get')
                ->with('fake2')
                ->atLeast()
                ->once()
                ->andReturn(null);
            $mock->shouldReceive('getUnsatisfied')->atLeast()->once();
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

        // with fallback locale
        app()->setLocale('es_ES');
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
        app()->setLocale('en');
    }
}

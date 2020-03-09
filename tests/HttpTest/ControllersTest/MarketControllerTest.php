<?php

namespace Tests;

use App\Services\Plugin;
use App\Services\PluginManager;
use App\Services\Unzip;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Tests\Concerns\MocksGuzzleClient;

class MarketControllerTest extends TestCase
{
    use MocksGuzzleClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(factory(\App\Models\User::class)->states('superAdmin')->create());
    }

    public function testDownload()
    {
        $this->setupGuzzleClientMock();

        $this->appendToGuzzleQueue(200, [], json_encode([
            'version' => 1,
            'packages' => [],
        ]));
        $this->postJson('/admin/plugins/market/download', ['name' => 'nope'])
            ->assertJson([
                'code' => 1,
                'message' => trans('admin.plugins.market.non-existent', ['plugin' => 'nope']),
            ]);

        // Unresolved plugin.
        $fakeRegistry = json_encode(['packages' => [
            [
                'name' => 'fake',
                'version' => '0.0.0',
                'require' => ['a' => '^4.0.0'],
            ],
        ]]);
        $this->appendToGuzzleQueue([new Response(200, [], $fakeRegistry)]);
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
        $fakeRegistry = json_encode(['packages' => [
            [
                'name' => 'fake',
                'version' => '0.0.0',
                'dist' => ['url' => 'http://nowhere.test/', 'shasum' => 'deadbeef'],
            ],
        ]]);
        $this->appendToGuzzleQueue([
            new Response(200, [], $fakeRegistry),
            new Response(404),
        ]);
        $this->postJson('/admin/plugins/market/download', ['name' => 'fake'])
            ->assertJson(['code' => 1]);

        $this->appendToGuzzleQueue([
            new Response(200, [], $fakeRegistry),
            new Response(200),
        ]);
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
        $this->setupGuzzleClientMock([
            new RequestException('Connection Error', new Request('POST', '')),
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

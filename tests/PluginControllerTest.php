<?php

namespace Tests;

use App\Services\Plugin;
use App\Services\PluginManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PluginControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAs('superAdmin');
    }

    public function testShowManage()
    {
        $this->get('/admin/plugins/manage')
            ->assertSee(trans('general.plugin-manage'));
    }

    public function testConfig()
    {
        option(['plugins_enabled' => json_encode([
            ['name' => 'fake3', 'version' => '0.0.0'],
            ['name' => 'fake4', 'version' => '0.0.0'],
        ])]);
        $this->mock(PluginManager::class, function ($mock) {
            $mock->shouldReceive('get')
                ->with('fake1')
                ->once()
                ->andReturn(null);

            $mock->shouldReceive('get')
                ->with('fake2')
                ->once()
                ->andReturn(new Plugin('', []));

            $mock->shouldReceive('get')
                ->with('fake3')
                ->once()
                ->andReturn(new Plugin('', []));

            $plugin = new Plugin(resource_path(''), ['config' => 'common/favicon.blade.php']);
            $plugin->setEnabled(true);
            $mock->shouldReceive('get')
                ->with('fake4')
                ->once()
                ->andReturn($plugin);
        });

        // No such plugin.
        $this->get('/admin/plugins/config/fake1')
            ->assertNotFound();

        // Plugin is disabled
        $this->get('/admin/plugins/config/fake2')
            ->assertNotFound();

        // Plugin is enabled but it doesn't have config view
        $this->get('/admin/plugins/config/fake3')
            ->assertSee(trans('admin.plugins.operations.no-config-notice'))
            ->assertNotFound();

        // Plugin has config view
        $this->get('/admin/plugins/config/fake4')
            ->assertSuccessful();

        option(['plugins_enabled' => '[]']);
    }

    public function testManage()
    {
        $this->mock(PluginManager::class, function ($mock) {
            $mock->shouldReceive('get')
                ->with('nope')
                ->once()
                ->andReturn(null);

            $mock->shouldReceive('get')
                ->with('fake1')
                ->once()
                ->andReturn(new Plugin('', []));

            $mock->shouldReceive('get')
                ->with('fake2')
                ->once()
                ->andReturn(new Plugin('', ['name' => 'fake2']));
            $mock->shouldReceive('getUnsatisfied')
                ->withArgs(function ($plugin) {
                    $this->assertEquals('fake2', $plugin->name);
                    return true;
                })
                ->once()
                ->andReturn(collect([
                    'dep' => ['version' => '0.0.0', 'constraint' => '^6.6.6'],
                    'whatever' => ['version' => null, 'constraint' => '^1.2.3'],
                ]));

            $mock->shouldReceive('get')
                ->with('fake3')
                ->once()
                ->andReturn(new Plugin('', ['name' => 'fake3', 'title' => 'Fake']));
            $mock->shouldReceive('enable')
                ->with('fake3')
                ->once();
            $mock->shouldReceive('getUnsatisfied')
                ->withArgs(function ($plugin) {
                    $this->assertEquals('fake3', $plugin->name);
                    return true;
                })
                ->once()
                ->andReturn(collect([]));

            $mock->shouldReceive('get')
                ->with('fake4')
                ->once()
                ->andReturn(new Plugin('', ['name' => 'fake4', 'title' => 'Fake']));
            $mock->shouldReceive('disable')
                ->with('fake4')
                ->once();

            $mock->shouldReceive('get')
                ->with('fake5')
                ->once()
                ->andReturn(new Plugin('', ['name' => 'fake5', 'title' => 'Fake']));
            $mock->shouldReceive('delete')
                ->with('fake5')
                ->once();
        });

        // An not-existed plugin
        $this->postJson('/admin/plugins/manage', ['name' => 'nope'])
            ->assertJson([
                'code' => 1,
                'message' => trans('admin.plugins.operations.not-found'),
            ]);

        // Invalid action
        $this->postJson('/admin/plugins/manage', ['name' => 'fake1'])
            ->assertJson([
                'code' => 1,
                'message' => trans('admin.invalid-action'),
            ]);

        // Enable a plugin with unsatisfied dependencies
        $this->postJson('/admin/plugins/manage', [
            'name' => 'fake2',
            'action' => 'enable',
        ])->assertJson([
            'code' => 1,
            'message' => trans('admin.plugins.operations.unsatisfied.notice'),
            'data' => [
                'reason' => [
                    trans('admin.plugins.operations.unsatisfied.version', [
                        'name' => 'dep',
                        'constraint' => '^6.6.6',
                    ]),
                    trans('admin.plugins.operations.unsatisfied.disabled', [
                        'name' => 'whatever',
                    ]),
                ],
            ],
        ]);

        // Enable a plugin
        $this->postJson('/admin/plugins/manage', [
            'name' => 'fake3',
            'action' => 'enable',
        ])->assertJson([
            'code' => 0,
            'message' => trans(
                'admin.plugins.operations.enabled',
                ['plugin' => 'Fake']
            ),
        ]);

        // Disable a plugin
        $this->postJson('/admin/plugins/manage', [
            'name' => 'fake4',
            'action' => 'disable',
        ])->assertJson([
            'code' => 0,
            'message' => trans(
                'admin.plugins.operations.disabled',
                ['plugin' => 'Fake']
            ),
        ]);

        // Delete a plugin
        $this->postJson('/admin/plugins/manage', [
            'name' => 'fake5',
            'action' => 'delete',
        ])->assertJson([
            'code' => 0,
            'message' => trans('admin.plugins.operations.deleted'),
        ]);
    }

    public function testGetPluginData()
    {
        $this->mock(PluginManager::class, function ($mock) {
            $mock->shouldReceive('all')
                ->once()
                ->andReturn(collect([new Plugin('', [
                    'name' => 'a',
                    'version' => '0.0.0',
                    'title' => ''
                ])]));
            $mock->shouldReceive('getUnsatisfied')
                ->withArgs(function ($plugin) {
                    $this->assertEquals('a', $plugin->name);
                    return true;
                })
                ->once()
                ->andReturn(collect(['b' => null]));
        });
        $this->getJson('/admin/plugins/data')
            ->assertJsonStructure([
                [
                    'name',
                    'version',
                    'title',
                    'description',
                    'author',
                    'url',
                    'enabled',
                    'config',
                    'dependencies',
                ],
            ]);
    }
}

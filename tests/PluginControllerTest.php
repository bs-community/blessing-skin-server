<?php

namespace Tests;

use App\Events;
use ZipArchive;
use Illuminate\Support\Facades\File;
use Tests\Concerns\GeneratesFakePlugins;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PluginControllerTest extends TestCase
{
    use DatabaseTransactions;
    use GeneratesFakePlugins;

    protected function setUp()
    {
        parent::setUp();

        $this->generateFakePlugin(['name' => 'fake-plugin-for-test', 'version' => '1.1.4']);
        $this->generateFakePlugin(['name' => 'fake-plugin-with-config-view', 'version' => '5.1.4', 'config' => 'config.blade.php']);

        return $this->actAs('superAdmin');
    }

    public function testShowManage()
    {
        $this->get('/admin/plugins/manage')
            ->assertSee(trans('general.plugin-manage'));
    }

    public function testConfig()
    {
        // Plugin is disabled
        $this->get('/admin/plugins/config/fake-plugin-with-config-view')
            ->assertNotFound();

        // Plugin is enabled but it doesn't have config view
        plugin('fake-plugin-for-test')->setEnabled(true);
        $this->get('/admin/plugins/config/avatar-api')
            ->assertSee(e(trans('admin.plugins.operations.no-config-notice')))
            ->assertNotFound();

        // Plugin has config view
        plugin('fake-plugin-with-config-view')->setEnabled(true);
        $this->get('/admin/plugins/config/fake-plugin-with-config-view')
            ->assertSuccessful();
    }

    public function testManage()
    {
        // An not-existed plugin
        $this->postJson('/admin/plugins/manage', ['name' => 'nope'])
            ->assertJson([
                'errno' => 1,
                'msg' => trans('admin.plugins.operations.not-found')
            ]);

        // Invalid action
        $this->postJson('/admin/plugins/manage', ['name' => 'fake-plugin-for-test'])
            ->assertJson([
                'errno' => 1,
                'msg' => trans('admin.invalid-action')
            ]);

        // Enable a plugin with unsatisfied dependencies
        app('plugins')->getPlugin('fake-plugin-for-test')->setRequirements([
            'blessing-skin-server' => '^3.4.0 || ^4.0.0',
            'fake-plugin-with-config-view' => '^6.6.6',
            'whatever' => '^1.0.0'
        ]);
        app('plugins')->enable('fake-plugin-with-config-view');
        $this->postJson('/admin/plugins/manage', [
            'name' => 'fake-plugin-for-test',
            'action' => 'enable'
        ])->assertJson([
            'errno' => 1,
            'msg' => trans('admin.plugins.operations.unsatisfied.notice'),
            'reason' => [
                trans('admin.plugins.operations.unsatisfied.version', [
                    'name' => 'fake-plugin-with-config-view',
                    'constraint' => '^6.6.6'
                ]),
                trans('admin.plugins.operations.unsatisfied.disabled', [
                    'name' => 'whatever'
                ])
            ]
        ]);

        // Enable a plugin
        app('plugins')->getPlugin('fake-plugin-for-test')->setRequirements([]);
        $this->postJson('/admin/plugins/manage', [
            'name' => 'fake-plugin-for-test',
            'action' => 'enable'
        ])->assertJson([
            'errno' => 0,
            'msg' => trans(
                'admin.plugins.operations.enabled',
                ['plugin' => plugin('fake-plugin-for-test')->title]
            )
        ]);

        // Disable a plugin
        $this->postJson('/admin/plugins/manage', [
            'name' => 'fake-plugin-for-test',
            'action' => 'disable'
        ])->assertJson([
            'errno' => 0,
            'msg' => trans(
                'admin.plugins.operations.disabled',
                ['plugin' => plugin('fake-plugin-for-test')->title]
            )
        ]);

        // Delete a plugin
        $this->postJson('/admin/plugins/manage', [
            'name' => 'fake-plugin-for-test',
            'action' => 'delete'
        ])->assertJson([
            'errno' => 0,
            'msg' => trans('admin.plugins.operations.deleted')
        ]);
        $this->assertFalse(file_exists(base_path('plugins/fake-plugin-for-test/')));
    }

    public function testGetPluginData()
    {
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
                    'dependencies'
                ]
            ]);
    }

    protected function tearDown()
    {
        // Clean fake plugins
        File::deleteDirectory(base_path('plugins/fake-plugin-for-test'));
        File::deleteDirectory(base_path('plugins/fake-plugin-with-config-view'));

        parent::tearDown();
    }
}

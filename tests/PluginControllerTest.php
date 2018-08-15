<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PluginControllerTest extends TestCase
{
    use GeneratesFakePlugins;
    use DatabaseTransactions;

    protected function setUp()
    {
        parent::setUp();

        $this->generateFakePlugin(['name' => 'fake-plugin-for-test', 'version' => '1.1.4']);
        $this->generateFakePlugin(['name' => 'fake-plugin-with-config-view', 'version' => '5.1.4', 'config' => 'config.blade.php']);

        return $this->actAs('superAdmin');
    }

    public function testShowManage()
    {
        $this->visit('/admin/plugins/manage')
            ->see(trans('general.plugin-manage'));
    }

    public function testConfig()
    {
        // Plugin is disabled
        $this->get('/admin/plugins/config/fake-plugin-with-config-view')
            ->see(trans('admin.plugins.operations.no-config-notice'))
            ->assertResponseStatus(404);

        // Plugin is enabled but it doesn't have config view
        plugin('fake-plugin-for-test')->setEnabled(true);
        $this->get('/admin/plugins/config/fake-plugin-for-test')
            ->see(trans('admin.plugins.operations.no-config-notice'))
            ->assertResponseStatus(404);

        // Plugin has config view
        plugin('fake-plugin-with-config-view')->setEnabled(true);
        $this->get('/admin/plugins/config/fake-plugin-with-config-view')
            ->assertResponseStatus(200);
    }

    public function testManage()
    {
        // An not-existed plugin
        $this->post('/admin/plugins/manage', ['name' => 'nope'])
            ->seeJson([
                'errno' => 1,
                'msg' => trans('admin.plugins.operations.not-found')
            ]);

        // Invalid action
        $this->post('/admin/plugins/manage', ['name' => 'fake-plugin-for-test'])
            ->seeJson([
                'errno' => 1,
                'msg' => trans('admin.invalid-action')
            ]);

        // Enable a plugin with unsatisfied dependencies
        app('plugins')->getPlugin('fake-plugin-for-test')->setRequirements([
            'blessing-skin-server' => '^3.4.0',
            'fake-plugin-with-config-view' => '^6.6.6',
            'whatever' => '^1.0.0'
        ]);
        app('plugins')->enable('fake-plugin-with-config-view');
        $this->post('/admin/plugins/manage', [
            'name' => 'fake-plugin-for-test',
            'action' => 'enable'
        ])->seeJson([
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
        $this->expectsEvents(App\Events\PluginWasEnabled::class);
        $this->post('/admin/plugins/manage', [
            'name' => 'fake-plugin-for-test',
            'action' => 'enable'
        ])->seeJson([
            'errno' => 0,
            'msg' => trans(
                'admin.plugins.operations.enabled',
                ['plugin' => plugin('fake-plugin-for-test')->title]
            )
        ]);

        // Disable a plugin
        $this->post('/admin/plugins/manage', [
            'name' => 'fake-plugin-for-test',
            'action' => 'disable'
        ])->seeJson([
            'errno' => 0,
            'msg' => trans(
                'admin.plugins.operations.disabled',
                ['plugin' => plugin('fake-plugin-for-test')->title]
            )
        ]);
        $this->expectsEvents(App\Events\PluginWasDisabled::class);

        // Delete a plugin
        $this->post('/admin/plugins/manage', [
            'name' => 'fake-plugin-for-test',
            'action' => 'delete'
        ])->seeJson([
            'errno' => 0,
            'msg' => trans('admin.plugins.operations.deleted')
        ]);
        $this->expectsEvents(App\Events\PluginWasDeleted::class);
        $this->assertFalse(file_exists(base_path('plugins/fake-plugin-for-test/')));
    }

    public function testGetPluginData()
    {
        $this->post('/admin/plugins/data')
            ->seeJsonStructure([
                'data' => [[
                    'name',
                    'version',
                    'path',
                    'title',
                    'description',
                    'author',
                    'url',
                    'namespace',
                    'enabled',
                    'dependencies'
                ]]
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

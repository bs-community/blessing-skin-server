<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PluginControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp()
    {
        parent::setUp();

        $plugins = [
            'example-plugin' => 'example-plugin_v1.0.0.zip',
            'hello-dolly'    => 'hello-dolly_v1.0.0.zip'
        ];

        foreach ($plugins as $plugin_name => $filename) {
            if (! file_exists(base_path('plugins/'.$plugin_name))) {
                $context = stream_context_create(['http' => [
                    'method' => 'GET',
                    'header' => 'User-Agent: '.config('secure.user_agent')
                ]]);

                file_put_contents(
                    storage_path('testing/'.$filename),
                    file_get_contents("https://coding.net/u/printempw/p/bs-plugins-archive/git/raw/master/$filename", false, $context)
                );

                $zip = new ZipArchive();
                $zip->open(storage_path('testing/'.$filename));
                $zip->extractTo(base_path('plugins/'));
                $zip->close();
                unlink(storage_path('testing/'.$filename));
            }
        }

        return $this->actAs('admin');
    }

    public function testShowManage()
    {
        $this->visit('/admin/plugins/manage')
            ->see(trans('general.plugin-manage'));
    }

    public function testConfig()
    {
        // Plugin is disabled
        $this->get('/admin/plugins/config/example-plugin')
            ->see(trans('admin.plugins.operations.no-config-notice'))
            ->assertResponseStatus(404);

        // Plugin is enabled but it doesn't have config view
        plugin('hello-dolly')->setEnabled(true);
        $this->get('/admin/plugins/config/hello-dolly')
            ->see(trans('admin.plugins.operations.no-config-notice'))
            ->assertResponseStatus(404);

        // Plugin has config view
        plugin('example-plugin')->setEnabled(true);
        $this->get('/admin/plugins/config/example-plugin')
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
        $this->post('/admin/plugins/manage', ['name' => 'hello-dolly'])
            ->seeJson([
                'errno' => 1,
                'msg' => trans('admin.invalid-action')
            ]);

        // Enable a plugin with unsatisfied dependencies
        app('plugins')->getPlugin('hello-dolly')->setRequirements([
            'blessing-skin-server' => '^3.4.0',
            'example-plugin' => '^6.6.6',
            'whatever' => '^1.0.0'
        ]);
        app('plugins')->enable('example-plugin');
        $this->post('/admin/plugins/manage', [
            'name' => 'hello-dolly',
            'action' => 'enable'
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('admin.plugins.operations.unsatisfied.notice'),
            'reason' => [
                trans('admin.plugins.operations.unsatisfied.version', [
                    'name' => 'example-plugin',
                    'constraint' => '^6.6.6'
                ]),
                trans('admin.plugins.operations.unsatisfied.disabled', [
                    'name' => 'whatever'
                ])
            ]
        ]);

        // Enable a plugin
        app('plugins')->getPlugin('hello-dolly')->setRequirements([]);
        $this->expectsEvents(App\Events\PluginWasEnabled::class);
        $this->post('/admin/plugins/manage', [
            'name' => 'hello-dolly',
            'action' => 'enable'
        ])->seeJson([
            'errno' => 0,
            'msg' => trans(
                'admin.plugins.operations.enabled',
                ['plugin' => plugin('hello-dolly')->title]
            )
        ]);

        // Disable a plugin
        $this->post('/admin/plugins/manage', [
            'name' => 'hello-dolly',
            'action' => 'disable'
        ])->seeJson([
            'errno' => 0,
            'msg' => trans(
                'admin.plugins.operations.disabled',
                ['plugin' => plugin('hello-dolly')->title]
            )
        ]);
        $this->expectsEvents(App\Events\PluginWasDisabled::class);

        // Delete a plugin
        $this->post('/admin/plugins/manage', [
            'name' => 'hello-dolly',
            'action' => 'delete'
        ])->seeJson([
            'errno' => 0,
            'msg' => trans('admin.plugins.operations.deleted')
        ]);
        $this->expectsEvents(App\Events\PluginWasDeleted::class);
        $this->assertFalse(file_exists(base_path('plugins/hello-dolly/')));
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
}

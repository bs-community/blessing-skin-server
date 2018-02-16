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
            'example-plugin' => 'example-plugin_v1.0.zip',
            'avatar-api'     => 'avatar-api_v1.1.zip'
        ];
        foreach ($plugins as $plugin_name => $filename) {
            if (! file_exists(base_path('plugins/'.$plugin_name))) {
                $context = stream_context_create(['http' => [
                    'method' => "GET",
                    'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.140 Safari/537.36"
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
        plugin('avatar-api')->setEnabled(true);
        $this->get('/admin/plugins/config/avatar-api')
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
        $this->post('/admin/plugins/manage', ['name' => 'avatar-api'])
            ->seeJson([
                'errno' => 1,
                'msg' => trans('admin.invalid-action')
            ]);

        // Enable a plugin
        $this->expectsEvents(App\Events\PluginWasEnabled::class);
        $this->post('/admin/plugins/manage', [
            'name' => 'avatar-api',
            'action' => 'enable'
        ])->seeJson([
            'errno' => 0,
            'msg' => trans(
                'admin.plugins.operations.enabled',
                ['plugin' => plugin('avatar-api')->title]
            )
        ]);

        // Disable a plugin
        $this->post('/admin/plugins/manage', [
            'name' => 'avatar-api',
            'action' => 'disable'
        ])->seeJson([
            'errno' => 0,
            'msg' => trans(
                'admin.plugins.operations.disabled',
                ['plugin' => plugin('avatar-api')->title]
            )
        ]);
        $this->expectsEvents(App\Events\PluginWasDisabled::class);

        // Delete a plugin
        $this->post('/admin/plugins/manage', [
            'name' => 'avatar-api',
            'action' => 'delete'
        ])->seeJson([
            'errno' => 0,
            'msg' => trans('admin.plugins.operations.deleted')
        ]);
        $this->expectsEvents(App\Events\PluginWasDeleted::class);
        $this->assertFalse(file_exists(base_path('plugins/avatar-api/')));
    }

    public function testGetPluginData()
    {
        $this->get('/admin/plugins/data')
            ->seeJsonStructure([
                'data' => [[
                    'name',
                    'version',
                    'path',
                    'title',
                    'description',
                    'author' => ['author', 'url'],
                    'url',
                    'namespace',
                    'status',
                    'operations' => ['enabled', 'hasConfigView']
                ]]
            ]);
    }
}

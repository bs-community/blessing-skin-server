<?php

namespace Tests;

use App\Services\Hook;
use Illuminate\Support\Facades\File;
use Tests\Concerns\GeneratesFakePlugins;

class HookTest extends TestCase
{
    use GeneratesFakePlugins;

    public function testAddMenuItem()
    {
        Hook::addMenuItem('user', 0, [
            'title' => 'Go to closet',
            'link' => '/user/closet',
            'icon' => 'fa-book'
        ]);
        $this->actAs('normal')
            ->get('/user')
            ->assertSee('Go to closet')
            ->assertSee('/user/closet')
            ->assertSee('fa-book');
    }

    public function testRegisterPluginTransScripts()
    {
        $this->generateFakePlugin(['name' => 'fake-plugin-with-i18n', 'version' => '0.0.1']);
        @mkdir($path = base_path('plugins/fake-plugin-with-i18n/lang/en'), 0755, true);
        file_put_contents("$path/locale.js", '');

        Hook::registerPluginTransScripts('fake-plugin-with-i18n');
        $this->get('/')
            ->assertSee('fake-plugin-with-i18n/lang/en/locale.js');

        File::deleteDirectory(base_path('plugins/fake-plugin-with-i18n'));
    }

    public function testAddStyleFileToPage()
    {
        Hook::addStyleFileToPage('/style/all');
        $this->get('/')
            ->assertSee('<link rel="stylesheet" href="/style/all">');

        Hook::addStyleFileToPage('/style/pattern', ['skinlib']);
        $this->get('/')
            ->assertDontSee('<link rel="stylesheet" href="/style/pattern">');
        $this->get('/skinlib')
            ->assertSee('<link rel="stylesheet" href="/style/pattern">');
    }

    public function testAddScriptFileToPage()
    {
        Hook::addScriptFileToPage('/script/all');
        $this->get('/')
            ->assertSee('<script src="/script/all"></script>');

        Hook::addScriptFileToPage('/script/pattern', ['skinlib']);
        $this->get('/')
            ->assertDontSee('<script src="/script/pattern"></script>');
        $this->get('/skinlib')
            ->assertSee('<script src="/script/pattern"></script>');
    }
}

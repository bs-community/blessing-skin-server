<?php

use App\Services\Hook;

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
            ->visit('/user')
            ->see('Go to closet')
            ->see('/user/closet')
            ->see('fa-book')
            ->click('Go to closet')
            ->seePageIs('/user/closet');
    }

    public function testRegisterPluginTransScripts()
    {
        $this->generateFakePlugin(['name' => 'fake-plugin-with-i18n', 'version' => '0.0.1']);
        @mkdir($path = base_path('plugins/fake-plugin-with-i18n/lang/en'), 0755, true);
        file_put_contents("$path/locale.js", '');

        Hook::registerPluginTransScripts('fake-plugin-with-i18n');
        $this->get('/')
            ->see('fake-plugin-with-i18n/lang/en/locale.js');

        File::deleteDirectory(base_path('plugins/fake-plugin-with-i18n'));
    }

    public function testAddStyleFileToPage()
    {
        Hook::addStyleFileToPage('/style/all');
        $this->visit('/')
            ->see('<link rel="stylesheet" href="/style/all">');

        Hook::addStyleFileToPage('/style/pattern', ['skinlib']);
        $this->visit('/')
            ->dontSee('<link rel="stylesheet" href="/style/pattern">');
        $this->visit('/skinlib')
            ->see('<link rel="stylesheet" href="/style/pattern">');
    }

    public function testAddScriptFileToPage()
    {
        Hook::addScriptFileToPage('/script/all');
        $this->visit('/')
            ->see('<script src="/script/all"></script>');

        Hook::addScriptFileToPage('/script/pattern', ['skinlib']);
        $this->visit('/')
            ->dontSee('<script src="/script/pattern"></script>');
        $this->visit('/skinlib')
            ->see('<script src="/script/pattern"></script>');
    }
}

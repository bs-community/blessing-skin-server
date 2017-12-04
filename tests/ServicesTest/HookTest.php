<?php

use App\Services\Hook;

class HookTest extends TestCase
{
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
        Hook::registerPluginTransScripts('example-plugin');
        $this->get('/')
            ->see('example-plugin/lang/en/locale.js');
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

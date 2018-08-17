<?php

namespace Tests;

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
            ->get('/user')
            ->assertSee('Go to closet')
            ->assertSee('/user/closet')
            ->assertSee('fa-book');
    }

    public function testRegisterPluginTransScripts()
    {
        Hook::registerPluginTransScripts('example-plugin');
        $this->get('/')
            ->assertSee('example-plugin/lang/en/locale.js');
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

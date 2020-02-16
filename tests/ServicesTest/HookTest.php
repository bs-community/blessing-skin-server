<?php

namespace Tests;

use App\Models\User;
use App\Services\Hook;

class HookTest extends TestCase
{
    public function testAddMenuItem()
    {
        Hook::addMenuItem('user', 0, [
            'title' => 'Link A',
            'link' => '/to/a',
            'icon' => 'fa-book',
            'new-tab' => true,
        ]);
        $this->actAs('normal')
            ->get('/user')
            ->assertSee('Link A')
            ->assertSee('/to/a')
            ->assertSee('target="_blank"')
            ->assertSee('fa-book');

        // Out of bound
        Hook::addMenuItem('user', 10, [
            'title' => 'Link B',
            'link' => '/to/b',
            'icon' => 'fa-book',
        ]);
        $this->actAs('normal')
            ->get('/user')
            ->assertSee('Link B')
            ->assertSee('/to/b');
    }

    public function testAddRoute()
    {
        Hook::addRoute(function ($route) {
            $route->any('/test-hook', function () {
            });
        });
        event(new \App\Events\ConfigureRoutes(resolve(\Illuminate\Routing\Router::class)));
        $this->get('/test-hook')->assertSuccessful();
    }

    public function testRegisterPluginTransScripts()
    {
        Hook::registerPluginTransScripts('fake-plugin-with-i18n', ['/']);
        $this->get('/')->assertSee('fake-plugin-with-i18n/lang/en/locale.js');
        $this->get('/skinlib')->assertDontSee('fake-plugin-with-i18n/lang/en/locale.js');
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

    public function testAddUserBadge()
    {
        Hook::addUserBadge('hi', 'green');
        $this->actAs('normal')
            ->get('/user')
            ->assertSee('<span class="badge bg-green mb-1 mr-2">hi</span>');
    }

    public function testSendNotification()
    {
        $user = factory(User::class)->create();
        Hook::sendNotification([$user], 'Ibara Mayaka');
        $user->refresh();
        $this->assertCount(1, $user->unreadNotifications);
    }

    public function testPushMiddleware()
    {
        Hook::pushMiddleware(Concerns\FakeMiddleware::class);
        $this->get('/')->assertHeader('X-Middleware-Test');
    }
}

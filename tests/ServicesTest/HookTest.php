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
        $this->actingAs(User::factory()->create())
            ->get('/user')
            ->assertSee('Link A')
            ->assertSee('/to/a')
            ->assertSee('target="_blank"', false)
            ->assertSee('fa-book');

        // Out of bound
        Hook::addMenuItem('user', 10, [
            'title' => 'Link B',
            'link' => '/to/b',
            'icon' => 'fa-book',
        ]);
        $this->actingAs(User::factory()->create())
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

    public function testAddStyleFileToPage()
    {
        Hook::addStyleFileToPage('/style/all');
        $this->get('/')
            ->assertSee('<link rel="stylesheet" href="/style/all" crossorigin="anonymous">', false);

        Hook::addStyleFileToPage('/style/pattern', ['skinlib']);
        $this->get('/')
            ->assertDontSee('<link rel="stylesheet" href="/style/pattern">');
        $this->get('/skinlib')
            ->assertSee('<link rel="stylesheet" href="/style/pattern" crossorigin="anonymous">', false);
    }

    public function testAddScriptFileToPage()
    {
        Hook::addScriptFileToPage('/script/all');
        $this->get('/')
            ->assertSee('<script src="/script/all" crossorigin="anonymous"></script>', false);

        Hook::addScriptFileToPage('/script/pattern', ['skinlib']);
        $this->get('/')
            ->assertDontSee('<script src="/script/pattern" crossorigin="anonymous"></script>');
        $this->get('/skinlib')
            ->assertSee('<script src="/script/pattern" crossorigin="anonymous"></script>', false);
    }

    public function testAddUserBadge()
    {
        Hook::addUserBadge('hi', 'green');
        $this->actingAs(User::factory()->create())
            ->get('/user')
            ->assertSee('<span class="badge bg-green mb-1 mr-2">hi</span>', false);
    }

    public function testSendNotification()
    {
        $user = User::factory()->create();
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

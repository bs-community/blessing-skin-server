<?php

namespace Tests;

use App\Events;
use App\Models\User;
use App\Services\Plugin;
use App\Services\PluginManager;
use Event;
use Symfony\Component\DomCrawler\Crawler;

class SideMenuComposerTest extends TestCase
{
    public function testEvents()
    {
        Event::fake();

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get('/user');
        Event::assertDispatched(Events\ConfigureUserMenu::class);
        Event::assertDispatched(Events\ConfigureExploreMenu::class);

        $this->get('/admin');
        Event::assertDispatched(Events\ConfigureAdminMenu::class);
    }

    public function testFilter()
    {
        $filter = Fakes\Filter::fake();

        $user = User::factory()->create();
        $this->actingAs($user)->get('/user');
        $filter->assertHaveBeenApplied('side_menu', function ($menu, $type) {
            $this->assertCount(count(config('menu.user')), $menu);
            $this->assertEquals('user', $type);

            return true;
        });
        $filter->assertApplied('side_menu', function ($menu, $type) {
            $this->assertCount(count(config('menu.explore')), $menu);
            $this->assertEquals('explore', $type);

            return true;
        });
    }

    public function testTransform()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $crawler = new Crawler($this->get('/user/oauth/manage')->getContent());
        $this->assertCount(1, $crawler->filter('aside .nav-treeview'));
        $this->assertCount(2, $crawler->filter('aside .nav-item.active'));
        $this->assertCount(2, $crawler->filter('aside .nav-link.active'));
    }

    public function testCollectPluginConfigs()
    {
        $this->mock(PluginManager::class, function ($mock) {
            $mock->shouldReceive('getEnabledPlugins')
                ->with()
                ->twice()
                ->andReturn(
                    collect(),
                    collect([
                        new Plugin(resource_path(''), [
                            'config' => 'user/master.blade.php',
                            'title' => 'Fake',
                            'name' => 'fake',
                        ]),
                    ])
                );
        });

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)
            ->get('/admin')
            ->assertDontSee(trans('general.plugin-configs'));

        $this->actingAs($admin)
            ->get('/admin')
            ->assertSee(trans('general.plugin-configs'))
            ->assertSee('fa-circle')
            ->assertSee('Fake')
            ->assertSee('admin/plugins/config/fake');
    }
}

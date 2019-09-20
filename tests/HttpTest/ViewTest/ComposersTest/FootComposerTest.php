<?php

namespace Tests;

use Event;
use App\Models\User;
use App\Services\Webpack;
use App\Services\Translations\JavaScript;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FootComposerTest extends TestCase
{
    use DatabaseTransactions;

    public function testInjectJavaScript()
    {
        option([
            'custom_js' => '"<div></div>"</script><h1 id=disallowed></h1><script>',
        ]);
        $user = factory(User::class)->make();
        $this->actingAs($user);
        $this->get('/user')->assertSee('"<div></div>"');
        $crawler = new Crawler($this->get('/user')->getContent());
        $this->assertCount(0, $crawler->filter('#disallowed'));

        config(['app.asset.env' => 'development']);
        $this->mock(JavaScript::class, function ($mock) {
            $mock->shouldReceive('generate')
                ->with('en')
                ->twice()
                ->andReturn('en.js');
            $mock->shouldReceive('plugin')
                ->with('en')
                ->twice()
                ->andReturn('en_plugin.js');
        });
        $this->mock(Webpack::class, function ($mock) {
            $mock->shouldReceive('url')->with('style.css');
            $mock->shouldReceive('url')->with('skins/skin-blue.min.css');
            $mock->shouldReceive('url')
                ->with('check-updates.js')
                ->once()
                ->andReturn('check-updates.js');
            $mock->shouldReceive('url')
                ->with('style.js')
                ->atLeast(1)
                ->andReturn('style.js');
            $mock->shouldReceive('url')
                ->with('index.js')
                ->twice()
                ->andReturn('index.js');
        });

        $this->get('/user')
            ->assertSee('en.js')
            ->assertSee('en_plugin.js')
            ->assertSee('index.js')
            ->assertDontSee('check-updates.js');

        $superAdmin = factory(User::class, 'superAdmin')->make();
        $this->actingAs($superAdmin);
        $this->get('/admin')->assertSee('check-updates.js');
    }

    public function testAddExtra()
    {
        Event::listen(\App\Events\RenderingFooter::class, function ($event) {
            $event->contents[] = '<div id=appended></div>';
        });

        $user = factory(User::class)->make();
        $this->actingAs($user);
        $this->get('/user')->assertSee('<div id=appended></div>');
    }
}

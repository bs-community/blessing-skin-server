<?php

namespace Tests;

use App\Models\User;
use App\Services\Translations\JavaScript;
use App\Services\Webpack;
use Event;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\DomCrawler\Crawler;

class FootComposerTest extends TestCase
{
    use DatabaseTransactions;

    public function testInjectJavaScript()
    {
        option([
            'custom_js' => '"<div></div>"</script><h1 id=disallowed></h1><script>',
        ]);
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->get('/user')->assertSee('"<div></div>"', false);
        $crawler = new Crawler($this->get('/user')->getContent());
        $this->assertCount(0, $crawler->filter('#disallowed'));

        config(['app.asset.env' => 'development']);
        $this->mock(JavaScript::class, function ($mock) {
            $mock->shouldReceive('generate')
                ->with('en')
                ->once()
                ->andReturn('en.js');
        });
        $this->mock(Webpack::class, function ($mock) {
            $mock->shouldReceive('url')->with('style.css');
            $mock->shouldReceive('url')->with('skins/skin-blue.min.css');
            $mock->shouldReceive('url')
                ->with('style.js')
                ->atLeast(1)
                ->andReturn('style.js');
            $mock->shouldReceive('url')
                ->with('app.js')
                ->once()
                ->andReturn('app.js');
        });

        $this->get('/user')->assertSee('en.js')->assertSee('app.js');
    }

    public function testAddExtra()
    {
        Event::listen(\App\Events\RenderingFooter::class, function ($event) {
            $event->contents[] = '<div id=appended></div>';
        });

        $user = User::factory()->create();
        $this->actingAs($user);
        $this->get('/user')->assertSee('<div id=appended></div>', false);
    }
}

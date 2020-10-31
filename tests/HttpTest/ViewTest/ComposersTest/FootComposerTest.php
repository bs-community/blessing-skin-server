<?php

namespace Tests;

use App\Models\User;
use App\Services\Translations\JavaScript;
use Event;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\DomCrawler\Crawler;

class FootComposerTest extends TestCase
{
    use DatabaseTransactions;

    public function testInjectJavaScript()
    {
        $this->mock(JavaScript::class, function ($mock) {
            $mock->shouldReceive('generate')
                ->with('en')
                ->atLeast(1)
                ->andReturn('en.js');
        });
        option([
            'custom_js' => '"<div></div>"</script><h1 id=disallowed></h1><script>',
        ]);
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->get('/user')
            ->assertSee('"<div></div>"', false)
            ->assertSee('en.js');
        $crawler = new Crawler($this->get('/user')->getContent());
        $this->assertCount(0, $crawler->filter('#disallowed'));
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

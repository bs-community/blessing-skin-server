<?php

namespace Tests;

use Event;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class HeadComposerTest extends TestCase
{
    use DatabaseTransactions;

    public function testAddFavicon()
    {
        $this->get('/')->assertSee(config('options.favicon_url'));

        option(['favicon_url' => '/a']);
        $this->get('/')->assertSee(url('/a'));

        option(['favicon_url' => 'http://example.com/icon']);
        $this->get('/')->assertSee('http://example.com/icon');
    }

    public function testApplyThemeColor()
    {
        $crawler = new Crawler($this->get('/')->getContent());
        $this->assertCount(1, $crawler->filter('meta[name="theme-color"]'));
    }

    public function testSeo()
    {
        option([
            'meta_keywords' => 'kw',
            'meta_description' => 'desc',
            'meta_extras' => '<meta name=fake><div id=disallowed></div>'
        ]);
        $crawler = new Crawler($this->get('/')->getContent());
        $this->assertEquals(
            'kw',
            $crawler->filter('meta[name=keywords]')->attr('content')
        );
        $this->assertEquals(
            'desc',
            $crawler->filter('meta[name=description]')->attr('content')
        );
        $this->assertCount(1, $crawler->filter('meta[name=fake]'));
        $this->assertCount(0, $crawler->filter('div#disallowed'));
    }

    public function testInjectStyles()
    {
        option(['custom_css' => 'div {} <style><div id=disallowed></div></style>']);

        $this->get('/')->assertSee('div {}');

        $crawler = new Crawler($this->get('/')->getContent());
        $this->assertCount(0, $crawler->filter('div#disallowed'));
    }

    public function testAddExtra()
    {
        Event::listen(\App\Events\RenderingHeader::class, function ($event) {
            $event->contents[] = '<meta name=appended>';
        });

        $this->get('/')->assertSee('<meta name=appended>');
    }
}

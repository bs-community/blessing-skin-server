<?php

use App\Events\RenderingHeader;
use App\Events\RenderingFooter;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class HomeControllerTest extends TestCase
{
    public function testIndex()
    {
        $this->get('/')
            ->assertSee(option_localized('site_name'))
            ->assertSee(option_localized('site_description'))
            ->assertViewHas('home_pic_url', option('home_pic_url'));
    }

    public function testRenderingHeaderEvent()
    {
        Event::listen(RenderingHeader::class, function (RenderingHeader $event) {
            $event->addContent('testing custom header');
        });
        $this->get('/')->assertSee('testing custom header');

        Event::listen(RenderingHeader::class, function (RenderingHeader $event) {
            $event->addContent(new stdClass());
        });
        $this->get('/');
    }

    public function testRenderingFooterEvent()
    {
        Event::listen(RenderingFooter::class, function (RenderingFooter $event) {
            $event->addContent('testing custom footer');
        });
        $this->get('/')->assertSee('testing custom footer');

        Event::listen(RenderingFooter::class, function (RenderingFooter $event) {
            $event->addContent(new stdClass());
        });
        $this->get('/');
    }
}

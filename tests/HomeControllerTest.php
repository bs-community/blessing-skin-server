<?php

namespace Tests;

use stdClass;
use App\Events\RenderingFooter;
use App\Events\RenderingHeader;
use Illuminate\Support\Facades\Event;

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

    public function testApiRoot()
    {
        $this->get('/api')->assertJson(['spec' => 0]);
    }
}

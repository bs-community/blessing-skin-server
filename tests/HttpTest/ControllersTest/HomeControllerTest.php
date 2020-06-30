<?php

namespace Tests;

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
    }

    public function testRenderingFooterEvent()
    {
        Event::listen(RenderingFooter::class, function (RenderingFooter $event) {
            $event->addContent('testing custom footer');
        });
        $this->get('/')->assertSee('testing custom footer');
    }

    public function testApiRoot()
    {
        $this->get('/api')->assertJson([
            'blessing_skin' => config('app.version'),
            'spec' => 0,
            'copyright' => 'Powered with ❤ by Blessing Skin Server.',
            'site_name' => option('site_name'),
        ]);
    }
}

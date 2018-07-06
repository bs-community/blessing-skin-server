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
            ->see(option_localized('site_name'))
            ->see(option_localized('site_description'))
            ->assertViewHas('home_pic_url', option('home_pic_url'));

        $this->visit('/')->click('Log In')->seePageIs('/auth/login');
        $this->visit('/')->click('#btn-register')->seePageIs('/auth/register');

        // Nav bar
        $this->visit('/')->click('Homepage')->seePageIs('/');
        $this->visit('/')->click('Skin Library')->seePageIs('/skinlib');
    }

    public function testRenderingHeaderEvent()
    {
        Event::listen(RenderingHeader::class, function (RenderingHeader $event) {
            $event->addContent('testing custom header');
        });
        $this->visit('/')->see('testing custom header');

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
        $this->visit('/')->see('testing custom footer');

        Event::listen(RenderingFooter::class, function (RenderingFooter $event) {
            $event->addContent(new stdClass());
        });
        $this->get('/');
    }
}

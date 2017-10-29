<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class HomeControllerTest extends TestCase
{
    public function testIndex()
    {
        $this->get('/')
            ->see(option('site_name'))
            ->see(option('site_description'))
            ->assertViewHas('home_pic_url', option('home_pic_url'));

        $this->visit('/')->click('Log In')->seePageIs('/auth/login');
        $this->visit('/')->click('#btn-register')->seePageIs('/auth/register');

        // Nav bar
        $this->visit('/')->click('Homepage')->seePageIs('/');
        $this->visit('/')->click('Skin Library')->seePageIs('/skinlib');
    }
}

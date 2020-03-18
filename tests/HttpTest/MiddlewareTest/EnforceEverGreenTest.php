<?php

namespace Tests;

class EnforceEverGreenTest extends TestCase
{
    public function testHandle()
    {
        $this->get('/', ['user-agent' => 'MSIE'])
            ->assertSee(trans('errors.http.ie'));
        $this->get('/', ['user-agent' => 'Trident'])
            ->assertSee(trans('errors.http.ie'));
        $this->get('/', [
            'user-agent' => 'AppleWebKit/537.36 Chrome/54.0.2403.157 Safari/537.36',
        ])->assertSee(trans('errors.http.ie'));
    }
}

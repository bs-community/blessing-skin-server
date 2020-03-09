<?php

namespace Tests;

class ForbiddenIETest extends TestCase
{
    public function testHandle()
    {
        $this->get('/', ['user-agent' => 'MSIE'])
            ->assertSee(trans('errors.http.ie'));
        $this->get('/', ['user-agent' => 'Trident'])
            ->assertSee(trans('errors.http.ie'));
    }
}

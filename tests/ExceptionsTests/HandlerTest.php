<?php

namespace Tests;

use Illuminate\Support\Str;

class HandlerTest extends TestCase
{
    public function testRenderAjaxException()
    {
        $json = $this->get('/abc', ['Accept' => 'application/json'])->json();
        $this->assertIsString($json['message']);
        $this->assertTrue($json['exception']);
        $this->assertTrue(collect($json['trace'])->every(
            fn ($trace) => Str::startsWith($trace['file'], 'app') && is_int($trace['line'])
        ));
    }
}

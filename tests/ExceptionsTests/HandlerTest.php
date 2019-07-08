<?php

namespace Tests;

use Illuminate\Support\Str;

class HandlerTest extends TestCase
{
    public function testRenderAjaxException()
    {
        $json = $this->get('/abc', ['Accept' => 'application/json'])->decodeResponseJson();
        $this->assertIsString($json['message']);
        $this->assertTrue($json['exception']);
        $this->assertTrue(collect($json['trace'])->every(function ($trace) {
            return Str::startsWith($trace['file'], 'app') && is_int($trace['line']);
        }));
    }
}

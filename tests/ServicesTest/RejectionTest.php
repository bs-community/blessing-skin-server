<?php

namespace Tests;

use App\Services\Rejection;

class RejectionTest extends TestCase
{
    public function testGetReason()
    {
        $reason = 'rejected';
        $rejection = new Rejection($reason);
        $this->assertEquals($reason, $rejection->getReason());
    }

    public function testGetData()
    {
        $rejection = new Rejection('', 'data');
        $this->assertEquals('data', $rejection->getData());

        $rejection = new Rejection('', ['a' => 'b']);
        $this->assertEquals('b', $rejection->getData('a'));
        $this->assertNull($rejection->getData('nope'));
        $this->assertEquals('default', $rejection->getData('nope', 'default'));
        $this->assertEquals(['a' => 'b'], $rejection->getData());
    }
}

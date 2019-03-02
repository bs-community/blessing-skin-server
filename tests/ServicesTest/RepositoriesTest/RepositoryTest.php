<?php

namespace Tests;

use App\Services\Repositories\Repository;

class RepositoryTest extends TestCase
{
    public function testHas()
    {
        $repo = new Repository();
        $repo->push('a');
        $this->assertTrue($repo->has(0));
        $this->assertFalse($repo->has(1));
    }

    public function testGet()
    {
        $repo = new Repository();
        $repo->push('a');
        $this->assertEquals('a', $repo->get(0));
        $this->assertNull($repo->get(1));
        $this->assertEquals('b', $repo->get(1, 'b'));
    }

    public function testSet()
    {
        $repo = new Repository();
        $repo->set('k1', 'v1');
        $this->assertEquals('v1', $repo->get('k1'));

        $repo->set([
            'k2' => 'v2',
            'k3' => 'v3',
        ]);
        $this->assertEquals('v2', $repo->get('k2'));
        $this->assertEquals('v3', $repo->get('k3'));
    }

    public function testPush()
    {
        $repo = new Repository();
        $repo->push('a');
        $this->assertEquals('a', $repo->get(0));
    }

    public function testAll()
    {
        $repo = new Repository();
        $repo->set('k1', 'v1');
        $repo->set([
            'k2' => 'v2',
            'k3' => 'v3',
        ]);
        $repo->push('a');
        $this->assertArraySubset([
            'k1' => 'v1',
            'k2' => 'v2',
            'k3' => 'v3',
            0 => 'a',
        ], $repo->all());
    }

    public function testRemember()
    {
        $repo = new Repository();
        $repo->set('k1', 'v1');
        $this->assertEquals(
            'v1',
            $repo->remember('k1', function () {
            })
        );

        $this->assertEquals(
            'v2',
            $repo->remember('k2', function () {
                return 'v2';
            })
        );
    }

    public function testForget()
    {
        $repo = new Repository();
        $repo->set('k1', 'v1');
        $repo->forget('k1');
        $this->assertFalse($repo->has('k1'));

        $repo->set([
            'k2' => 'v2',
            'k3' => 'v3',
        ]);
        $repo->forget(['k2', 'k3']);
        $this->assertFalse($repo->has('k2'));
        $this->assertFalse($repo->has('k3'));
    }

    public function testOffsetExists()
    {
        $repo = new Repository();
        $repo->set('k1', 'v1');
        $this->assertTrue($repo->offsetExists('k1'));
    }

    public function testOffsetGet()
    {
        $repo = new Repository();
        $repo->push('a');
        $this->assertEquals('a', $repo->offsetGet(0));
        $this->assertNull($repo->get(1));
    }

    public function testOffsetSet()
    {
        $repo = new Repository();
        $repo->offsetSet('k1', 'v1');
        $this->assertEquals('v1', $repo->get('k1'));
    }

    public function testOffsetUnset()
    {
        $repo = new Repository();
        $repo->set('k1', 'v1');
        $repo->offsetUnset('k1');
        $this->assertFalse($repo->has('k1'));
    }
}

<?php

namespace Tests;

use App\Services\Repositories\Repository;

class RepositoryTest extends TestCase
{
    public function testHas()
    {
        $repo = new Repository();
        $repo->set('a', 'b');
        $this->assertTrue($repo->has('a'));
        $this->assertFalse($repo->has('b'));
    }

    public function testGet()
    {
        $repo = new Repository();
        $repo->set('a', 'b');
        $this->assertEquals('b', $repo->get('a'));
        $this->assertNull($repo->get('b'));
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
}

<?php

use App\Services\Repositories\OptionRepository;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OptionRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    public function testGet()
    {
        $repo = new OptionRepository();
        $repo->set('k1', '(null)');
        $this->assertNull($repo->get('k1'));
    }

    public function testSet()
    {
        $repo = new OptionRepository();
        $repo->set([
            'k1' => 'v1',
            'k2' => 'v2'
        ]);
        $this->assertEquals('v1', $repo->get('k1'));
        $this->assertEquals('v2', $repo->get('k2'));
    }

    public function testOnly()
    {
        $repo = new OptionRepository();
        $repo->set([
            'k1' => 'v1',
            'k2' => 'v2',
            'k3' => 'v3',
        ]);
        $this->assertArraySubset([
            'k1' => 'v1',
            'k2' => 'v2'
        ], $repo->only(['k1', 'k2']));
    }
}

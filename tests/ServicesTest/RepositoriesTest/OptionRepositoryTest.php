<?php

namespace Tests;

use App\Services\Repositories\OptionRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OptionRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    public function testGet()
    {
        $repo = new OptionRepository();
        $repo->set('k1', '(null)');
        $this->assertNull($repo->get('k1'));
        $this->assertNull(option()->get('k1'));
    }

    public function testSet()
    {
        $repo = new OptionRepository();
        $repo->set([
            'k1' => 'v1',
            'k2' => 'v2',
        ]);
        $this->assertEquals('v1', $repo->get('k1'));
        $this->assertEquals('v2', $repo->get('k2'));
    }
}

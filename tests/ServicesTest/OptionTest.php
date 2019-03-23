<?php

namespace Tests;

use App\Services\Option;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OptionTest extends TestCase
{
    use DatabaseTransactions;

    public function testGet()
    {
        $repo = new Option();
        $repo->set('k1', '(null)');
        $this->assertNull($repo->get('k1'));
        $this->assertNull(option()->get('k1'));
    }

    public function testSet()
    {
        $repo = new Option();
        $repo->set([
            'k1' => 'v1',
            'k2' => 'v2',
        ]);
        $this->assertEquals('v1', $repo->get('k1'));
        $this->assertEquals('v2', $repo->get('k2'));
    }
}

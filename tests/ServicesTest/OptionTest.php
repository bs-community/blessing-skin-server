<?php

namespace Tests;

use App\Services\Option;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OptionTest extends TestCase
{
    use DatabaseTransactions;

    public function testGet()
    {
        $options = resolve(Option::class);
        $options->set('k1', '(null)');
        $this->assertNull($options->get('k1'));
        $this->assertNull(option()->get('k1'));
    }

    public function testSet()
    {
        $options = resolve(Option::class);
        $options->set([
            'k1' => 'v1',
            'k2' => 'v2',
        ]);
        $this->assertEquals('v1', $options->get('k1'));
        $this->assertEquals('v2', $options->get('k2'));
    }

    public function testReadFromCache()
    {
        $this->mock(\Illuminate\Filesystem\Filesystem::class, function ($mock) {
            $path = storage_path('options.php');
            $mock->shouldReceive('exists')->with($path)->once()->andReturn(true);
            $mock->shouldReceive('getRequire')->with($path)->once()->andReturn(['k' => 'v']);
        });

        app()->forgetInstance(Option::class);
        $options = resolve(Option::class);
        $this->assertEquals('v', $options->get('k'));
    }
}

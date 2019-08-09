<?php

namespace Tests;

class SaltRandomCommandTest extends TestCase
{
    public function testGenerateSalt()
    {
        $result = bin2hex('deadbeef');
        $this->mock(\Illuminate\Contracts\Encryption\Encrypter::class, function ($mock) {
            $mock->shouldReceive('generateKey')->with('AES-128-CBC')->twice()->andReturn('deadbeef');
        });
        $this->artisan('salt:random')
            ->expectsOutput("Application salt [$result] set successfully.");
        $this->artisan('salt:random --show')
            ->expectsOutput($result);
    }
}

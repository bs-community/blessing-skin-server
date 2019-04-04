<?php

namespace Tests;

use App\Rules\Captcha;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;

class CaptchaTest extends TestCase
{
    public function testCharactersCaptcha()
    {
        app()->instance('captcha', new class {
            public function check()
            {
                return true;
            }
        });
        $rule = resolve(Captcha::class);
        $this->assertTrue($rule->passes('captcha', 'any'));
        $this->assertEquals(trans('validation.captcha'), $rule->message());
    }

    public function testRecaptcha()
    {
        option(['recaptcha_secretkey' => 'secret']);
        $mock = new MockHandler([
            new Response(403),
            new Response(200, [], json_encode(['success' => true]))
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $rule = new Captcha($client);
        $this->assertFalse($rule->passes('captcha', 'value'));
        $this->assertTrue($rule->passes('captcha', 'value'));
        $this->assertEquals(trans('validation.recaptcha'), $rule->message());
    }
}

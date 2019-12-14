<?php

namespace Tests;

use App\Rules\Captcha;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class CaptchaTest extends TestCase
{
    public function testCharactersCaptcha()
    {
        session(['captcha' => 'abc']);
        $rule = resolve(Captcha::class);
        $this->assertFalse($rule->passes('captcha', 'abcd'));
        $this->assertEquals(trans('validation.captcha'), $rule->message());
        $this->assertNull(session('captcha'));

        session(['captcha' => 'abc']);
        $rule = resolve(Captcha::class);
        $this->assertTrue($rule->passes('captcha', 'abc'));
        $this->assertNull(session('captcha'));
    }

    public function testRecaptcha()
    {
        option(['recaptcha_secretkey' => 'secret']);
        $mock = new MockHandler([
            new Response(403),
            new Response(200, [], json_encode(['success' => true])),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $rule = new Captcha($client);
        $this->assertFalse($rule->passes('captcha', 'value'));
        $this->assertTrue($rule->passes('captcha', 'value'));
        $this->assertEquals(trans('validation.recaptcha'), $rule->message());
    }
}

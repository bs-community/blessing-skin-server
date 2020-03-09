<?php

namespace Tests;

use App\Rules\Captcha;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

class CaptchaTest extends TestCase
{
    public function testCharactersCaptcha()
    {
        session(['captcha' => 'abc']);
        $rule = new Captcha();
        $this->assertFalse($rule->passes('captcha', 'abcd'));
        $this->assertEquals(trans('validation.captcha'), $rule->message());
        $this->assertNull(session('captcha'));

        session(['captcha' => 'abc']);
        $rule = new Captcha();
        $this->assertTrue($rule->passes('captcha', 'abc'));
        $this->assertNull(session('captcha'));
    }

    public function testRecaptcha()
    {
        option(['recaptcha_secretkey' => 'secret']);
        Http::fake(Http::response(['success' => true]));

        $rule = new Captcha();
        $this->assertTrue($rule->passes('captcha', 'value'));
        $this->assertEquals(trans('validation.recaptcha'), $rule->message());
        Http::assertSent(function (Request $request) {
            $this->assertEquals(
                ['secret' => 'secret', 'response' => 'value'],
                $request->data()
            );

            return true;
        });
    }
}

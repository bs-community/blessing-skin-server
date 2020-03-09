<?php

namespace App\Rules;

use Composer\CaBundle\CaBundle;
use Gregwar\Captcha\CaptchaBuilder;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Http;

class Captcha implements Rule
{
    public function passes($attribute, $value)
    {
        $secretkey = option('recaptcha_secretkey');
        if ($secretkey) {
            return Http::asForm()
                ->withOptions(['verify' => CaBundle::getSystemCaRootBundlePath()])
                ->post('https://www.recaptcha.net/recaptcha/api/siteverify', [
                    'secret' => $secretkey,
                    'response' => $value,
                ])
                ->json()['success'];
        }

        $builder = new CaptchaBuilder(session()->pull('captcha'));

        return $builder->testPhrase($value);
    }

    public function message()
    {
        return option('recaptcha_secretkey')
            ? trans('validation.recaptcha')
            : trans('validation.captcha');
    }
}

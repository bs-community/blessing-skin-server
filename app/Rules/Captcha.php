<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Captcha implements Rule
{
    public function passes($attribute, $value)
    {
        if (app()->environment('testing')) {
            return true;
        }

        $secretkey = option('recaptcha_secretkey');
        if ($secretkey) {
            $client = new \GuzzleHttp\Client();
            $response = $client->post('https://www.recaptcha.net/recaptcha/api/siteverify', [
                'form_params' => [
                    'secret' => $secretkey,
                    'response' => $value,
                ]
            ]);
            if ($response->getStatusCode() == 200) {
                $body = json_decode((string) $response->getBody());
                return $body->success;
            }
            return false;
        }

        return captcha_check($value);
    }

    public function message()
    {
        return option('recaptcha_secretkey')
            ? trans('validation.recaptcha')
            : trans('validation.captcha');
    }
}

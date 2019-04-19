<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Captcha implements Rule
{
    protected $client;

    public function __construct(\GuzzleHttp\Client $client)
    {
        $this->client = $client;
    }

    public function passes($attribute, $value)
    {
        $secretkey = option('recaptcha_secretkey');
        if ($secretkey) {
            try {
                $response = $this->client->post('https://www.recaptcha.net/recaptcha/api/siteverify', [
                    'form_params' => [
                        'secret' => $secretkey,
                        'response' => $value,
                    ],
                    'verify' => resource_path('misc/ca-bundle.crt'),
                ]);
                if ($response->getStatusCode() == 200) {
                    $body = json_decode((string) $response->getBody());

                    return $body->success;
                }
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                return false;
            }
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

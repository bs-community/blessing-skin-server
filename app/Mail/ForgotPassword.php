<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForgotPassword extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $url = '';

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function build()
    {
        $site_name = option_localized('site_name');

        return $this
            ->subject(trans('auth.forgot.mail.title', ['sitename' => $site_name]))
            ->view('mails.password-reset');
    }
}

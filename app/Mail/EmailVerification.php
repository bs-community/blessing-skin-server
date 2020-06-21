<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailVerification extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function build()
    {
        $site_name = option_localized('site_name');

        return $this
            ->subject(trans('user.verification.mail.title', ['sitename' => $site_name]))
            ->view('mails.email-verification');
    }
}

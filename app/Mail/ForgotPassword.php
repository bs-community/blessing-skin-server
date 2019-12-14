<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForgotPassword extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @var string
     */
    public $url = '';

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $url)
    {
        $this->url = $url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $site_name = option_localized('site_name');

        return $this->from(config('mail.username'), $site_name)
            ->subject(trans('auth.forgot.mail.title', ['sitename' => $site_name]))
            ->view('mails.password-reset');
    }
}

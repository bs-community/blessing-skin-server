<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ForgotPassword extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var string
     */
    public $reset_url = '';

    /**
     * Create a new message instance.
     *
     * @param string $url
     * @return void
     */
    public function __construct(string $url)
    {
        $this->reset_url = $url;
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
            ->subject(trans('auth.mail.title', ['sitename' => $site_name]))
            ->view('auth.mail');
    }
}

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
     * @param int    $uid
     * @param string $token
     * @return void
     */
    public function __construct(int $uid, string $token)
    {
        $this->reset_url = option('site_url')."/auth/reset?uid=$uid&token=$token";
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

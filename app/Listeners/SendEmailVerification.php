<?php

namespace App\Listeners;

use App\Mail\EmailVerification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class SendEmailVerification
{
    public function handle(User $user)
    {
        if (option('require_verification')) {
            $url = URL::temporarySignedRoute('auth.verify', Carbon::now()->addHour(), ['user' => $user, 'hash' => hash('sha256', $user->email)], false);

            try {
                Mail::to($user->email)->send(new EmailVerification(url($url)));
            } catch (\Exception $e) {
                report($e);
            }
        }
    }
}

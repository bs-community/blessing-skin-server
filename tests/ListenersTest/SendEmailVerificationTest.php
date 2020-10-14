<?php

namespace Tests;

use App\Mail\EmailVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

class SendEmailVerificationTest extends TestCase
{
    use DatabaseTransactions;

    public function testHandle()
    {
        Mail::fake();
        option(['require_verification' => true]);

        $user = User::factory()->create(['verified' => false]);
        Event::dispatch('auth.registration.completed', [$user]);
        Mail::assertSent(EmailVerification::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        Mail::shouldReceive('to')
            ->once()
            ->andThrow(new \Mockery\Exception('A fake exception.'));
        Event::dispatch('auth.registration.completed', [$user]);
    }
}

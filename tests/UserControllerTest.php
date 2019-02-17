<?php

namespace Tests;

use Parsedown;
use App\Events;
use App\Models\User;
use App\Mail\EmailVerification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testIndex()
    {
        $user = factory(User::class)->create();
        factory(\App\Models\Player::class)->create(['uid' => $user->uid]);

        $this->actAs($user)
            ->get('/user')
            ->assertViewHas('user')
            ->assertViewHas('statistics')
            ->assertSee((new Parsedown())->text(option_localized('announcement')))
            ->assertSee((string) $user->score);

        $unverified = factory(User::class)->create(['verified' => false]);
        $this->actAs($unverified)
            ->get('/user')
            ->assertDontSee(trans('user.verification.notice.title'));
    }

    public function testScoreInfo()
    {
        $user = factory(User::class)->create();
        factory(\App\Models\Player::class)->create(['uid' => $user->uid]);

        $this->actingAs($user)
            ->get('/user/score-info')
            ->assertJson([
                'user' => [
                    'score' => $user->score,
                    'lastSignAt' => $user->last_sign_at
                ],
                'stats' => [
                    'players' => [
                        'used' => 1,
                        'total' => 11,
                        'percentage' => 1 / 11 * 100
                    ],
                    'storage' => [
                        'used' => 0,
                        'total' => $user->score,
                        'percentage' => 0
                    ]
                ],
                'signAfterZero' => option('sign_after_zero'),
                'signGapTime' => option('sign_gap_time')
            ]);
    }

    public function testSign()
    {
        option(['sign_score' => '50,50']);
        $user = factory(User::class)->create();

        // Success
        $this->actAs($user)
            ->postJson('/user/sign')
            ->assertJson([
                'errno' => 0,
                'msg' => trans('user.sign-success', ['score' => 50]),
                'score' => option('user_initial_score') + 50,
                'storage' => [
                    'percentage' => 0,
                    'total' => option('user_initial_score') + 50,
                    'used' => 0
                ],
                'remaining_time' => (int) option('sign_gap_time')
            ]);

        // Remaining time is greater than 0
        $this->postJson('/user/sign')
            ->assertJson([
                'errno' => 1,
                /* 'msg' => trans(
                    'user.cant-sign-until',
                    [
                        'time' => option('sign_gap_time'),
                        'unit' => trans('user.time-unit-hour')
                    ]
                ) */
            ]);
        $user = User::find($user->uid);
        $this->assertEquals(option('sign_gap_time'), round($user->getSignRemainingTime() / 3600));

        // Can sign after 0 o'clock
        option(['sign_after_zero' => true]);
        $diff = \Carbon\Carbon::now()->diffInSeconds(\Carbon\Carbon::tomorrow());
        if ($diff / 3600 >= 1) {
            $diff = round($diff / 3600);
            $unit = 'hour';
        } else {
            $diff = round($diff / 60);
            $unit = 'min';
        }
        $this->postJson('/user/sign')
            ->assertJson([
                'errno' => 1,
                'msg' => trans(
                    'user.cant-sign-until',
                    [
                        'time' => $diff,
                        'unit' => trans("user.time-unit-$unit")
                    ]
                )
            ]);

        $user = factory(User::class)->create([
            'last_sign_at' => \Carbon\Carbon::today()->toDateTimeString()
        ]);
        $this->actAs($user)->postJson('/user/sign')
            ->assertJson([
                'errno' => 0
            ]);
    }

    public function testSendVerificationEmail()
    {
        Mail::fake();

        $unverified = factory(User::class)->create(['verified' => false]);
        $verified = factory(User::class)->create();

        // Should be forbidden if account verification is disabled
        option(['require_verification' => false]);
        $this->actingAs($unverified)
            ->postJson('/user/email-verification')
            ->assertJson([
                'errno' => 1,
                'msg' => trans('user.verification.disabled')
            ]);
        option(['require_verification' => true]);

        // Too frequent
        $this->actingAs($unverified)
            ->withSession([
                'last_mail_time' => time() - 10
            ])
            ->postJson('/user/email-verification')
            ->assertJson([
                'errno' => 1,
                'msg' => trans('user.verification.frequent-mail')
            ]);
        $this->flushSession();

        // Already verified
        $this->actingAs($verified)
            ->postJson('/user/email-verification')
            ->assertJson([
                'errno' => 1,
                'msg' => trans('user.verification.verified')
            ]);

        $this->actingAs($unverified)
            ->postJson('/user/email-verification')
            ->assertJson([
                'errno' => 0,
                'msg' => trans('user.verification.success')
            ]);
        Mail::assertSent(EmailVerification::class, function ($mail) use ($unverified) {
            return $mail->hasTo($unverified->email);
        });

        // Should handle exception when sending email
        Mail::shouldReceive('to')
            ->once()
            ->andThrow(new \Mockery\Exception('A fake exception.'));
        $this->flushSession();
        $this->actingAs($unverified)
            ->postJson('/user/email-verification')
            ->assertJson([
                'errno' => 2,
                'msg' => trans('user.verification.failed', ['msg' => 'A fake exception.'])
            ]);

        // Addition: Mailable test
        $site_name = option_localized('site_name');
        $mailable = new EmailVerification('url');
        $mailable->build();
        $this->assertTrue($mailable->hasFrom(config('mail.username'), $site_name));
        $this->assertEquals(trans('user.verification.mail.title', ['sitename' => $site_name]), $mailable->subject);
        $this->assertEquals('mails.email-verification', $mailable->view);
    }

    public function testProfile()
    {
        $this->actAs('normal')
            ->get('/user/profile')
            ->assertViewHas('user');
    }

    public function testHandleProfile()
    {
        $user = factory(User::class)->create();
        $user->changePassword('12345678');

        // Invalid action
        $this->actAs($user)
            ->postJson('/user/profile')
            ->assertJson([
                'errno' => 1,
                'msg' => trans('general.illegal-parameters')
            ]);

        // Change nickname without `new_nickname` field
        $this->postJson('/user/profile', [
            'action' => 'nickname'
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->assertJson([
            'errno' => 1,
            'msg' => trans('validation.required', ['attribute' => 'new nickname'])
        ]);

        // Invalid nickname
        $this->postJson('/user/profile', [
            'action' => 'nickname',
            'new_nickname' => '\\'
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->assertJson([
            'errno' => 1,
            'msg' => trans('validation.no_special_chars', ['attribute' => 'new nickname'])
        ]);

        // Too long nickname
        $this->postJson('/user/profile', [
            'action' => 'nickname',
            'new_nickname' => str_random(256)
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->assertJson([
            'errno' => 1,
            'msg' => trans('validation.max.string', ['attribute' => 'new nickname', 'max' => 255])
        ]);

        // Change nickname successfully
        $this->expectsEvents(Events\UserProfileUpdated::class);
        $this->postJson('/user/profile', [
            'action' => 'nickname',
            'new_nickname' => 'nickname'
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->assertJson([
            'errno' => 0,
            'msg' => trans('user.profile.nickname.success', ['nickname' => 'nickname'])
        ]);
        $this->assertEquals('nickname', User::find($user->uid)->nickname);

        // Change password without `current_password` field
        $this->postJson('/user/profile', [
            'action' => 'password'
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->assertJson([
            'errno' => 1,
            'msg' => trans('validation.required', ['attribute' => 'current password'])
        ]);

        // Too short current password
        $this->postJson('/user/profile', [
            'action' => 'password',
            'current_password' => '1',
            'new_password' => '12345678'
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->assertJson([
            'errno' => 1,
            'msg' => trans('validation.min.string', ['attribute' => 'current password', 'min' => 6])
        ]);

        // Too long current password
        $this->postJson('/user/profile', [
            'action' => 'password',
            'current_password' => str_random(33),
            'new_password' => '12345678'
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->assertJson([
            'errno' => 1,
            'msg' => trans('validation.max.string', ['attribute' => 'current password', 'max' => 32])
        ]);

        // Too short new password
        $this->postJson('/user/profile', [
            'action' => 'password',
            'current_password' => '12345678',
            'new_password' => '1'
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->assertJson([
            'errno' => 1,
            'msg' => trans('validation.min.string', ['attribute' => 'new password', 'min' => 8])
        ]);

        // Too long new password
        $this->postJson('/user/profile', [
            'action' => 'password',
            'current_password' => '12345678',
            'new_password' => str_random(33)
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->assertJson([
            'errno' => 1,
            'msg' => trans('validation.max.string', ['attribute' => 'new password', 'max' => 32])
        ]);

        // Wrong old password
        $this->postJson('/user/profile', [
            'action' => 'password',
            'current_password' => '1234567',
            'new_password' => '87654321'
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->assertJson([
            'errno' => 1,
            'msg' => trans('user.profile.password.wrong-password')
        ]);

        // Change password successfully
        $this->expectsEvents(Events\EncryptUserPassword::class);
        $this->postJson('/user/profile', [
            'action' => 'password',
            'current_password' => '12345678',
            'new_password' => '87654321'
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->assertJson([
            'errno' => 0,
            'msg' => trans('user.profile.password.success')
        ]);
        $this->assertTrue(User::find($user->uid)->verifyPassword('87654321'));
        // After changed password, user should re-login.
        $this->assertGuest();

        $user = User::find($user->uid);
        // Change email without `new_email` field
        $this->actAs($user)
            ->postJson(
                '/user/profile',
                ['action' => 'email'],
                ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertJson([
                'errno' => 1,
                'msg' => trans('validation.required', ['attribute' => 'new email'])
            ]);

        // Invalid email
        $this->postJson('/user/profile', [
            'action' => 'email',
            'new_email' => 'not_an_email'
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->assertJson([
            'errno' => 1,
            'msg' => trans('validation.email', ['attribute' => 'new email'])
        ]);

        // Too short current password
        $this->postJson('/user/profile', [
            'action' => 'email',
            'new_email' => 'a@b.c',
            'password' => '1'
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->assertJson([
            'errno' => 1,
            'msg' => trans('validation.min.string', ['attribute' => 'password', 'min' => 6])
        ]);

        // Too long current password
        $this->postJson('/user/profile', [
            'action' => 'email',
            'new_email' => 'a@b.c',
            'password' => str_random(33)
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->assertJson([
            'errno' => 1,
            'msg' => trans('validation.max.string', ['attribute' => 'password', 'max' => 32])
        ]);

        // Use a duplicated email
        $this->postJson('/user/profile', [
            'action' => 'email',
            'new_email' => $user->email,
            'password' => '87654321'
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->assertJson([
            'errno' => 1,
            'msg' => trans('user.profile.email.existed')
        ]);

        // Wrong password
        $this->postJson('/user/profile', [
            'action' => 'email',
            'new_email' => 'a@b.c',
            'password' => '7654321'
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->assertJson([
            'errno' => 1,
            'msg' => trans('user.profile.email.wrong-password')
        ]);

        // Change email successfully
        $this->postJson('/user/profile', [
            'action' => 'email',
            'new_email' => 'a@b.c',
            'password' => '87654321'
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->assertJson([
            'errno' => 0,
            'msg' => trans('user.profile.email.success')
        ]);
        $this->assertEquals('a@b.c', User::find($user->uid)->email);
        $this->assertEquals(0, User::find($user->uid)->verified);
        // After changed email, user should re-login.
        $this->assertGuest();

        $user = User::find($user->uid);
        $user->verified = true;
        $user->save();
        // Delete account without `password` field
        $this->actAs($user)
            ->postJson(
                '/user/profile',
                ['action' => 'delete'],
                ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertJson([
                'errno' => 1,
                'msg' => trans('validation.required', ['attribute' => 'password'])
            ]);

        // Too short current password
        $this->postJson('/user/profile', [
            'action' => 'delete',
            'password' => '1'
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->assertJson([
            'errno' => 1,
            'msg' => trans('validation.min.string', ['attribute' => 'password', 'min' => 6])
        ]);

        // Too long current password
        $this->postJson('/user/profile', [
            'action' => 'delete',
            'password' => str_random(33)
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->assertJson([
            'errno' => 1,
            'msg' => trans('validation.max.string', ['attribute' => 'password', 'max' => 32])
        ]);

        // Wrong password
        $this->postJson('/user/profile', [
            'action' => 'delete',
            'password' => '7654321'
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->assertJson([
            'errno' => 1,
            'msg' => trans('user.profile.delete.wrong-password')
        ]);

        // Delete account successfully
        $this->postJson('/user/profile', [
            'action' => 'delete',
            'password' => '87654321'
        ])->assertJson([
            'errno' => 0,
            'msg' => trans('user.profile.delete.success')
        ]);
        $this->assertNull(User::find($user->uid));

        // Administrator cannot be deleted
        $this->actAs('admin')
            ->postJson('/user/profile', [
            'action' => 'delete',
            'password' => '87654321'
        ])->assertJson([
            'errno' => 1,
            'msg' => trans('user.profile.delete.admin')
        ]);
    }

    public function testSetAvatar()
    {
        $user = factory(User::class)->create();
        $steve = factory(\App\Models\Texture::class)->create();
        $cape = factory(\App\Models\Texture::class, 'cape')->create();

        // Without `tid` field
        $this->actAs($user)
            ->postJson('/user/profile/avatar', [], [
                'X-Requested-With' => 'XMLHttpRequest'
            ])
            ->assertJson([
                'errno' => 1,
                'msg' => trans('validation.required', ['attribute' => 'tid'])
            ]);

        // TID is not a integer
        $this->actAs($user)
            ->postJson('/user/profile/avatar', [
                'tid' => 'string'
            ], [
                'X-Requested-With' => 'XMLHttpRequest'
            ])
            ->assertJson([
                'errno' => 1,
                'msg' => trans('validation.integer', ['attribute' => 'tid'])
            ]);

        // Texture cannot be found
        $this->actAs($user)
            ->postJson('/user/profile/avatar', [
                'tid' => 0
            ])
            ->assertJson([
                'errno' => 1,
                'msg' => trans('skinlib.non-existent')
            ]);

        // Use cape
        $this->actAs($user)
            ->postJson('/user/profile/avatar', [
                'tid' => $cape->tid
            ])
            ->assertJson([
                'errno' => 1,
                'msg' => trans('user.profile.avatar.wrong-type')
            ]);

        // Success
        $this->actAs($user)
            ->postJson('/user/profile/avatar', [
                'tid' => $steve->tid
            ])
            ->assertJson([
                'errno' => 0,
                'msg' => trans('user.profile.avatar.success')
            ]);
        $this->assertEquals($steve->tid, User::find($user->uid)->avatar);
    }
}

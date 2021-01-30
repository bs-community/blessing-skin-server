<?php

namespace Tests;

use App\Events;
use App\Mail\EmailVerification;
use App\Models\Texture;
use App\Models\User;
use Blessing\Filter;
use Blessing\Rejection;
use Carbon\Carbon;
use Event;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use League\CommonMark\GithubFlavoredMarkdownConverter;

class UserControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testIndex()
    {
        $filter = Fakes\Filter::fake();

        $user = User::factory()->create();
        $uid = $user->uid;
        \App\Models\Player::factory()->create(['uid' => $uid]);

        $converter = new GithubFlavoredMarkdownConverter();
        $announcement = $converter->convertToHtml(option_localized('announcement'));
        $this->actingAs($user)
            ->get('/user')
            ->assertSee($announcement, false);
        $filter->assertApplied('grid:user.index');
        $filter->assertApplied('user_avatar', function ($url, $user) use ($uid) {
            $this->assertTrue(Str::contains($url, '/avatar/'.$user->avatar));
            $this->assertEquals($uid, $user->uid);

            return true;
        });

        $unverified = User::factory()->create(['verified' => false]);
        $this->actingAs($unverified)
            ->get('/user')
            ->assertDontSee(trans('user.verification.notice.title'));
    }

    public function testScoreInfo()
    {
        $user = User::factory()->create();
        \App\Models\Player::factory()->create(['uid' => $user->uid]);

        $this->actingAs($user)
            ->get('/user/score-info')
            ->assertJson([
                'user' => [
                    'score' => $user->score,
                    'lastSignAt' => $user->last_sign_at,
                ],
                'rate' => [
                    'storage' => (int) option('score_per_storage'),
                    'players' => (int) option('score_per_player'),
                ],
                'usage' => [
                    'players' => 1,
                    'storage' => 0,
                ],
                'signAfterZero' => option('sign_after_zero'),
                'signGapTime' => option('sign_gap_time'),
            ]);
    }

    public function testSign()
    {
        Event::fake();
        $filter = Fakes\Filter::fake();
        option(['sign_score' => '50,50']);
        $user = User::factory()->create();

        // success
        $this->actingAs($user)
            ->postJson('/user/sign')
            ->assertJson([
                'code' => 0,
                'message' => trans('user.sign-success', ['score' => 50]),
                'data' => [
                    'score' => option('user_initial_score') + 50,
                ],
            ]);
        $filter->assertApplied('sign_score', function ($score) {
            $this->assertEquals(50, $score);

            return true;
        });
        Event::assertDispatched('user.sign.before', function ($eventName, $payload) {
            $this->assertEquals(50, $payload[0]);

            return true;
        });
        Event::assertDispatched('user.sign.after', function ($eventName, $payload) {
            $this->assertEquals(50, $payload[0]);

            return true;
        });

        // remaining time is greater than 0
        Event::fake();
        $user = User::factory()->create(['last_sign_at' => Carbon::now()]);
        option(['sign_gap_time' => 2]);
        $this->actingAs($user)
            ->postJson('/user/sign')
            ->assertJson(['code' => 1]);
        Event::assertNotDispatched('user.sign.before');
        Event::assertNotDispatched('user.sign.after');

        // can sign after 0 o'clock
        Event::fake();
        option(['sign_after_zero' => true]);
        $user = User::factory()->create(['last_sign_at' => Carbon::now()]);
        $this->actingAs($user)
            ->postJson('/user/sign')
            ->assertJson(['code' => 1]);
        Event::assertNotDispatched('user.sign.before');
        Event::assertNotDispatched('user.sign.after');

        $user = User::factory()->create([
            'last_sign_at' => Carbon::today(),
        ]);
        $this->actingAs($user)
            ->postJson('/user/sign')
            ->assertJson(['code' => 0]);

        // rejected
        Event::fake();
        $filter->add('can_sign', function () {
            return new Rejection('rejected');
        });
        $this
            ->postJson('/user/sign')
            ->assertJson(['code' => 2, 'message' => 'rejected']);
        Event::assertNotDispatched('user.sign.before');
        Event::assertNotDispatched('user.sign.after');
    }

    public function testSendVerificationEmail()
    {
        Mail::fake();

        $unverified = User::factory()->create(['verified' => false]);
        $verified = User::factory()->create();

        // Should be forbidden if account verification is disabled
        option(['require_verification' => false]);
        $this->actingAs($unverified)
            ->postJson('/user/email-verification')
            ->assertJson([
                'code' => 1,
                'message' => trans('user.verification.disabled'),
            ]);
        option(['require_verification' => true]);

        // Too frequent
        $this->actingAs($unverified)
            ->withSession([
                'last_mail_time' => time() - 10,
            ])
            ->postJson('/user/email-verification')
            ->assertJson([
                'code' => 1,
                'message' => trans('user.verification.frequent-mail'),
            ]);
        $this->flushSession();

        // Already verified
        $this->actingAs($verified)
            ->postJson('/user/email-verification')
            ->assertJson([
                'code' => 1,
                'message' => trans('user.verification.verified'),
            ]);

        $this->actingAs($unverified)
            ->postJson('/user/email-verification')
            ->assertJson([
                'code' => 0,
                'message' => trans('user.verification.success'),
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
                'code' => 2,
                'message' => trans('user.verification.failed', ['msg' => 'A fake exception.']),
            ]);

        // Addition: Mailable test
        $site_name = option_localized('site_name');
        $mailable = new EmailVerification('url');
        $mailable->build();
        $this->assertEquals(trans('user.verification.mail.title', ['sitename' => $site_name]), $mailable->subject);
        $this->assertEquals('mails.email-verification', $mailable->view);
    }

    public function testProfile()
    {
        $filter = Fakes\Filter::fake();

        $this->actingAs(User::factory()->create())
            ->get('/user/profile')
            ->assertViewIs('user.profile');
        $filter->assertApplied('grid:user.profile');
    }

    public function testHandleProfile()
    {
        Event::fake();
        $user = User::factory()->create();
        $user->changePassword('12345678');
        $uid = $user->uid;

        // Rejected by filter
        $filter = resolve(Filter::class);
        $filter->add('user_can_edit_profile', function ($can, $action, $addition) {
            $this->assertEquals('nope', $action);
            $this->assertEquals([], $addition);

            return new Rejection('rejected');
        });
        $this->actingAs($user)
            ->postJson('/user/profile', ['action' => 'nope'])
            ->assertJson(['code' => 1, 'message' => 'rejected']);
        $filter->remove('user_can_edit_profile');

        // Invalid action
        $this->actingAs($user)
            ->postJson('/user/profile')
            ->assertJson([
                'code' => 1,
                'message' => trans('general.illegal-parameters'),
            ]);
        Event::assertDispatched('user.profile.updating', function ($eventName, $payload) use ($uid) {
            [$user, $action, $addition] = $payload;
            $this->assertEquals($uid, $user->uid);
            $this->assertEquals('', $action);
            $this->assertEquals([], $addition);

            return true;
        });

        // Change nickname without `new_nickname` field
        $this->postJson('/user/profile', ['action' => 'nickname'])
            ->assertJsonValidationErrors('new_nickname');

        // Change nickname successfully
        $this->postJson('/user/profile', [
            'action' => 'nickname',
            'new_nickname' => 'nickname',
        ])->assertJson([
            'code' => 0,
            'message' => trans('user.profile.nickname.success', ['nickname' => 'nickname']),
        ]);
        $this->assertEquals('nickname', User::find($user->uid)->nickname);
        Event::assertDispatched('user.profile.updated', function ($eventName, $payload) use ($uid) {
            [$user, $action, $addition] = $payload;
            $this->assertEquals($uid, $user->uid);
            $this->assertEquals('nickname', $action);
            $this->assertEquals(['new_nickname' => 'nickname'], $addition);

            return true;
        });
        Event::assertDispatched(Events\UserProfileUpdated::class);
        Event::fake();

        // Change password without `current_password` field
        $this->postJson('/user/profile', ['action' => 'password'])
            ->assertJsonValidationErrors('current_password');

        // Too short current password
        $this->postJson('/user/profile', [
            'action' => 'password',
            'current_password' => '1',
            'new_password' => '12345678',
        ])->assertJsonValidationErrors('current_password');

        // Too long current password
        $this->postJson('/user/profile', [
            'action' => 'password',
            'current_password' => Str::random(33),
            'new_password' => '12345678',
        ])->assertJsonValidationErrors('current_password');

        // Too short new password
        $this->postJson('/user/profile', [
            'action' => 'password',
            'current_password' => '12345678',
            'new_password' => '1',
        ])->assertJsonValidationErrors('new_password');

        // Too long new password
        $this->postJson('/user/profile', [
            'action' => 'password',
            'current_password' => '12345678',
            'new_password' => Str::random(33),
        ])->assertJsonValidationErrors('new_password');

        // Wrong old password
        $this->postJson('/user/profile', [
            'action' => 'password',
            'current_password' => '1234567',
            'new_password' => '87654321',
        ])->assertJson([
            'code' => 1,
            'message' => trans('user.profile.password.wrong-password'),
        ]);

        // Change password successfully
        $filter = Fakes\Filter::fake();
        $this->postJson('/user/profile', [
            'action' => 'password',
            'current_password' => '12345678',
            'new_password' => '87654321',
        ])->assertJson([
            'code' => 0,
            'message' => trans('user.profile.password.success'),
        ]);
        Event::assertDispatched('user.profile.updated', function ($eventName, $payload) use ($uid) {
            [$user, $action, $addition] = $payload;
            $this->assertEquals($uid, $user->uid);
            $this->assertEquals('password', $action);
            $this->assertEquals([
                'current_password' => '12345678',
                'new_password' => '87654321',
            ], $addition);

            return true;
        });
        $filter->assertApplied('verify_password', function ($passed, $raw, $u) use ($user) {
            $this->assertEquals('12345678', $raw);
            $this->assertTrue($user->is($u));

            return true;
        });
        $filter->assertApplied('user_password', function ($password) {
            $this->assertTrue(password_verify('87654321', $password));

            return true;
        });
        $this->assertTrue(User::find($user->uid)->verifyPassword('87654321'));
        // After changed password, user should re-login.
        $this->assertGuest();
        Event::fake();

        $user = User::find($user->uid);
        // Change email without `email` field
        $this->actingAs($user)
            ->postJson(
                '/user/profile',
                ['action' => 'email']
            )
            ->assertJsonValidationErrors('email');

        // Invalid email
        $this->postJson('/user/profile', [
            'action' => 'email',
            'email' => 'not_an_email',
        ])->assertJsonValidationErrors('email');

        // Too short current password
        $this->postJson('/user/profile', [
            'action' => 'email',
            'email' => 'a@b.c',
            'password' => '1',
        ])->assertJsonValidationErrors('password');

        // Too long current password
        $this->postJson('/user/profile', [
            'action' => 'email',
            'email' => 'a@b.c',
            'password' => Str::random(33),
        ])->assertJsonValidationErrors('password');

        // Use a duplicated email
        $this->postJson('/user/profile', [
            'action' => 'email',
            'email' => $user->email,
            'password' => '87654321',
        ])->assertJson([
            'code' => 1,
            'message' => trans('user.profile.email.existed'),
        ]);

        // Wrong password
        $this->postJson('/user/profile', [
            'action' => 'email',
            'email' => 'a@b.c',
            'password' => '7654321',
        ])->assertJson([
            'code' => 1,
            'message' => trans('user.profile.email.wrong-password'),
        ]);

        // Change email successfully
        $this->postJson('/user/profile', [
            'action' => 'email',
            'email' => 'a@b.c',
            'password' => '87654321',
        ])->assertJson([
            'code' => 0,
            'message' => trans('user.profile.email.success'),
        ]);
        Event::assertDispatched('user.profile.updated', function ($eventName, $payload) use ($uid) {
            [$user, $action, $addition] = $payload;
            $this->assertEquals($uid, $user->uid);
            $this->assertEquals('email', $action);
            $this->assertEquals([
                'email' => 'a@b.c',
                'password' => '87654321',
            ], $addition);

            return true;
        });
        $this->assertEquals('a@b.c', User::find($user->uid)->email);
        $this->assertEquals(0, User::find($user->uid)->verified);
        // After changed email, user should re-login.
        $this->assertGuest();
        Event::fake();

        $user = User::find($user->uid);
        $user->verified = true;
        $user->save();
        // Delete account without `password` field
        $this->actingAs($user)
            ->postJson(
                '/user/profile',
                ['action' => 'delete']
            )
            ->assertJsonValidationErrors('password');

        // Too short current password
        $this->postJson('/user/profile', [
            'action' => 'delete',
            'password' => '1',
        ])->assertJsonValidationErrors('password');

        // Too long current password
        $this->postJson('/user/profile', [
            'action' => 'delete',
            'password' => Str::random(33),
        ])->assertJsonValidationErrors('password');

        // Wrong password
        $this->postJson('/user/profile', [
            'action' => 'delete',
            'password' => '7654321',
        ])->assertJson([
            'code' => 1,
            'message' => trans('user.profile.delete.wrong-password'),
        ]);

        // Delete account successfully
        $this->postJson('/user/profile', [
            'action' => 'delete',
            'password' => '87654321',
        ])->assertJson([
            'code' => 0,
            'message' => trans('user.profile.delete.success'),
        ]);
        Event::assertDispatched('user.deleting', function ($eventName, $payload) use ($uid) {
            $this->assertEquals($uid, $payload[0]->uid);

            return true;
        });
        Event::assertDispatched('user.deleted', function ($eventName, $payload) use ($uid) {
            $this->assertEquals($uid, $payload[0]->uid);

            return true;
        });
        $this->assertNull(User::find($user->uid));

        // Administrator cannot be deleted
        $this->actingAs(User::factory()->admin()->create())
            ->postJson('/user/profile', [
            'action' => 'delete',
            'password' => '87654321',
        ])->assertJson([
            'code' => 1,
            'message' => trans('user.profile.delete.admin'),
        ]);
    }

    public function testSetAvatar()
    {
        $user = User::factory()->create();
        $uid = $user->uid;
        $steve = Texture::factory()->create();
        $cape = Texture::factory()->cape()->create();

        // without `tid` field
        $this->actingAs($user)
            ->postJson('/user/profile/avatar')
            ->assertJsonValidationErrors('tid');

        // `tid` is not a integer
        $this->actingAs($user)
            ->postJson('/user/profile/avatar', ['tid' => 'string'])
            ->assertJsonValidationErrors('tid');

        // texture cannot be found
        $this->actingAs($user)
            ->postJson('/user/profile/avatar', ['tid' => -1])
            ->assertJson([
                'code' => 1,
                'message' => trans('skinlib.non-existent'),
            ]);

        // use cape
        $this->actingAs($user)
            ->postJson('/user/profile/avatar', ['tid' => $cape->tid])
            ->assertJson([
                'code' => 1,
                'message' => trans('user.profile.avatar.wrong-type'),
            ]);

        // use private texture
        $private = Texture::factory()->private()->create();
        $this->actingAs($user)
            ->postJson('/user/profile/avatar', ['tid' => $private->tid])
            ->assertJson([
                'code' => 1,
                'message' => trans('skinlib.show.private'),
            ]);

        // success
        Event::fake();
        $this->actingAs($user)
            ->postJson('/user/profile/avatar', ['tid' => $steve->tid])
            ->assertJson([
                'code' => 0,
                'message' => trans('user.profile.avatar.success'),
            ]);
        $this->assertEquals($steve->tid, User::find($user->uid)->avatar);
        Event::assertDispatched(
            'user.avatar.updating',
            function ($eventName, $payload) use ($uid, $steve) {
                [$user, $tid] = $payload;
                $this->assertEquals($uid, $user->uid);
                $this->assertEquals($steve->tid, $tid);

                return true;
            }
        );
        Event::assertDispatched(
            'user.avatar.updated',
            function ($eventName, $payload) use ($uid, $steve) {
                [$user, $tid] = $payload;
                $this->assertEquals($uid, $user->uid);
                $this->assertEquals($steve->tid, $tid);

                return true;
            }
        );

        // reset avatar
        Event::fake();
        $this->postJson('/user/profile/avatar', ['tid' => 0])
            ->assertJson(['code' => 0]);
        $this->assertEquals(0, User::find($user->uid)->avatar);
        Event::assertDispatched(
            'user.avatar.updated',
            function ($eventName, $payload) use ($uid) {
                [$user, $tid] = $payload;
                $this->assertEquals($uid, $user->uid);
                $this->assertEquals(0, $tid);

                return true;
            }
        );

        // rejected by filter
        $filter = resolve(Filter::class);
        $filter->add('user_can_update_avatar', function ($can, $user, $tid) use ($uid, $steve) {
            $this->assertEquals($uid, $user->uid);
            $this->assertEquals($steve->tid, $tid);

            return new Rejection('rejected');
        });
        $this->actingAs($user)
            ->postJson('/user/profile/avatar', ['tid' => $steve->tid])
            ->assertJson(['code' => 1, 'message' => 'rejected']);
    }
}

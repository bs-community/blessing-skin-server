<?php

namespace Tests;

use Cache;
use App\Events;
use App\Models\User;
use App\Models\Player;
use Illuminate\Support\Str;
use App\Mail\ForgotPassword;
use App\Services\Facades\Option;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AuthControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        app()->instance(\App\Rules\Captcha::class, new class extends \App\Rules\Captcha {
            public function __construct(\GuzzleHttp\Client $client = null)
            {
            }

            public function passes($attribute, $value)
            {
                return true;
            }
        });
    }

    public function testLogin()
    {
        $this->get('/auth/login')->assertSee('Log in');
    }

    public function testHandleLogin()
    {
        $this->expectsEvents(Events\UserTryToLogin::class);
        $this->expectsEvents(Events\UserLoggedIn::class);

        $user = factory(User::class)->create();
        $user->changePassword('12345678');
        $player = factory(Player::class)->create(['uid' => $user->uid]);

        // Should return a warning if `identification` is empty
        $this->postJson('/auth/login')->assertJsonValidationErrors('identification');

        // Should return a warning if `password` is empty
        $this->postJson(
            '/auth/login', ['identification' => $user->email]
        )->assertJsonValidationErrors('password');

        // Should return a warning if length of `password` is lower than 6
        $this->postJson(
            '/auth/login', [
            'identification' => $user->email,
            'password' => '123',
        ])->assertJsonValidationErrors('password');

        // Should return a warning if length of `password` is greater than 32
        $this->postJson(
            '/auth/login', [
            'identification' => $user->email,
            'password' => Str::random(80),
        ])->assertJsonValidationErrors('password');

        $this->flushSession();

        $loginFailsCacheKey = sha1('login_fails_'.get_client_ip());

        // Logging in should be failed if password is wrong
        $this->postJson(
            '/auth/login', [
            'identification' => $user->email,
            'password' => 'wrong-password',
        ])->assertJson(
            [
                'code' => 1,
                'message' => trans('auth.validation.password'),
                'login_fails' => 1,
            ]
        );
        $this->assertTrue(Cache::has($loginFailsCacheKey));

        $this->flushSession();

        // Should check captcha if there are too many fails
        Cache::put($loginFailsCacheKey, 4);
        $this->postJson(
                '/auth/login', [
                'identification' => $user->email,
                'password' => '12345678',
            ])->assertJsonValidationErrors('captcha');

        Cache::flush();
        $this->flushSession();

        // Should return a warning if user isn't existed
        $this->postJson(
            '/auth/login', [
            'identification' => 'nope@nope.net',
            'password' => '12345678',
        ])->assertJson([
            'code' => 2,
            'message' => trans('auth.validation.user'),
        ]);

        $this->flushSession();

        // Should clean the `login_fails` session if logged in successfully
        Cache::put($loginFailsCacheKey, 1);
        $this->postJson('/auth/login', [
            'identification' => $user->email,
            'password' => '12345678',
        ])->assertJson(
            [
                'code' => 0,
                'message' => trans('auth.login.success'),
            ]
        );
        $this->assertFalse(Cache::has($loginFailsCacheKey));

        Cache::flush();
        $this->flushSession();

        // Logged in should be in success if logged in with player name
        $this->postJson(
            '/auth/login', [
            'identification' => $player->name,
            'password' => '12345678',
        ]
        )->assertJson(
            [
                'code' => 0,
                'message' => trans('auth.login.success'),
            ]
        );
        $this->assertAuthenticated();
    }

    public function testLogout()
    {
        $this->postJson('/auth/logout')
            ->assertJson([
                'code' => 1,
                'message' => trans('auth.logout.fail'),
            ]);

        $user = factory(User::class)->create();
        $this->actingAs($user)->postJson('/auth/logout')->assertJson(
            [
                'code' => 0,
                'message' => trans('auth.logout.success'),
            ]
        );
        $this->assertGuest();
    }

    public function testRegister()
    {
        $this->get('/auth/register')->assertSee('Register');

        option(['user_can_register' => false]);
        $this->get('/auth/register')->assertSee(trans('auth.register.close'));
    }

    public function testHandleRegister()
    {
        $this->expectsEvents(Events\UserRegistered::class);

        // Should return a warning if `email` is empty
        $this->postJson('/auth/register')->assertJsonValidationErrors('email');

        // Should return a warning if `email` is invalid
        $this->postJson(
            '/auth/register',
            ['email' => 'not_an_email']
        )->assertJsonValidationErrors('email');

        // An existed user
        $existedUser = factory(User::class)->create();
        $this->postJson(
            '/auth/register',
            ['email' => $existedUser->email]
        )->assertJsonValidationErrors('email');

        // Should return a warning if `password` is empty
        $this->postJson(
            '/auth/register',
            ['email' => 'a@b.c']
        )->assertJsonValidationErrors('password');

        // Should return a warning if length of `password` is lower than 8
        $this->postJson(
            '/auth/register',
            [
                'email' => 'a@b.c',
                'password' => '1',
            ]
        )->assertJsonValidationErrors('password');

        // Should return a warning if length of `password` is greater than 32
        $this->postJson(
            '/auth/register',
            [
                'email' => 'a@b.c',
                'password' => Str::random(33),
            ]
        )->assertJsonValidationErrors('password');

        // The register_with_player_name option is set to true by default.
        // Should return a warning if `player_name` is empty
        $this->postJson(
            '/auth/register',
            [
                'email' => 'a@b.c',
                'password' => '12345678',
                'captcha' => 'a',
            ]
        )->assertJsonValidationErrors('player_name');

        // Should return a warning if `player_name` is invalid
        option(['player_name_rule' => 'official']);
        $this->postJson(
            '/auth/register',
            [
                'email' => 'a@b.c',
                'password' => '12345678',
                'player_name' => '角色名',
                'captcha' => 'a',
            ]
        )->assertJsonValidationErrors('player_name');

        // Should return a warning if `player_name` is too long
        $this->postJson(
            '/auth/register',
            [
                'email' => 'a@b.c',
                'password' => '12345678',
                'player_name' => Str::random(option('player_name_length_max') + 10),
                'captcha' => 'a',
            ]
        )->assertJsonValidationErrors('player_name');

        // Existed player
        $player = factory(Player::class)->create();
        $this->postJson(
            '/auth/register',
            [
                'email' => 'a@b.c',
                'password' => '12345678',
                'player_name' => $player->name,
                'captcha' => 'a',
            ]
        )->assertJson([
            'code' => 2,
            'message' => trans('user.player.add.repeated'),
        ]);
        $this->assertNull(User::where('email', 'a@b.c')->first());

        option(['register_with_player_name' => false]);

        // Should return a warning if `nickname` is empty
        $this->postJson(
            '/auth/register',
            [
                'email' => 'a@b.c',
                'password' => '12345678',
                'captcha' => 'a',
            ]
        )->assertJsonValidationErrors('nickname');

        // Should return a warning if `nickname` is invalid
        $this->postJson(
            '/auth/register',
            [
                'email' => 'a@b.c',
                'password' => '12345678',
                'nickname' => '\\',
                'captcha' => 'a',
            ]
        )->assertJsonValidationErrors('nickname');

        // Should return a warning if `nickname` is too long
        $this->postJson(
            '/auth/register',
            [
                'email' => 'a@b.c',
                'password' => '12345678',
                'nickname' => Str::random(256),
                'captcha' => 'a',
            ]
        )->assertJsonValidationErrors('nickname');

        // Should return a warning if `captcha` is empty
        $this->postJson(
            '/auth/register',
            [
                'email' => 'a@b.c',
                'password' => '12345678',
                'nickname' => 'nickname',
            ]
        )->assertJsonValidationErrors('captcha');

        // Should be forbidden if registering is closed
        Option::set('user_can_register', false);
        $this->postJson(
            '/auth/register',
            [
                'email' => 'a@b.c',
                'password' => '12345678',
                'nickname' => 'nickname',
                'captcha' => 'a',
            ]
        )->assertJson([
            'code' => 7,
            'message' => trans('auth.register.close'),
        ]);

        // Reopen for test
        Option::set('user_can_register', true);

        // Should be forbidden if registering's count current IP is over
        Option::set('regs_per_ip', -1);
        $this->postJson(
            '/auth/register',
            [
                'email' => 'a@b.c',
                'password' => '12345678',
                'nickname' => 'nickname',
                'captcha' => 'a',
            ]
        )->assertJson([
            'code' => 7,
            'message' => trans('auth.register.max', ['regs' => option('regs_per_ip')]),
        ]);

        Option::set('regs_per_ip', 100);

        // Database should be updated if succeeded
        $response = $this->postJson(
            '/auth/register',
            [
                'email' => 'a@b.c',
                'password' => '12345678',
                'nickname' => 'nickname',
                'captcha' => 'a',
            ]
        );
        $newUser = User::where('email', 'a@b.c')->first();
        $response->assertJson([
            'code' => 0,
            'message' => trans('auth.register.success'),
        ]);
        $this->assertTrue($newUser->verifyPassword('12345678'));
        $this->assertDatabaseHas('users', [
            'email' => 'a@b.c',
            'nickname' => 'nickname',
            'score' => option('user_initial_score'),
            'ip' => '127.0.0.1',
            'permission' => User::NORMAL,
        ]);
        $this->assertAuthenticated();

        // Require player name
        option(['register_with_player_name' => true]);
        $this->postJson(
            '/auth/register',
            [
                'email' => 'abc@test.org',
                'password' => '12345678',
                'player_name' => 'name',
                'captcha' => 'a',
            ]
        )->assertJson(['code' => 0]);
        $this->assertNotNull(Player::where('player', 'name'));
    }

    public function testForgot()
    {
        $this->get('/auth/forgot')->assertSee('Forgot Password');

        config(['mail.driver' => '']);
        $this->get('/auth/forgot')->assertSee(trans('auth.forgot.disabled'));
    }

    public function testHandleForgot()
    {
        Mail::fake();

        // Should be forbidden if "forgot password" is closed
        config(['mail.driver' => '']);
        $this->postJson('/auth/forgot', [
            'captcha' => 'a',
        ])->assertJson([
            'code' => 1,
            'message' => trans('auth.forgot.disabled'),
        ]);
        config(['mail.driver' => 'smtp']);

        $lastMailCacheKey = sha1('last_mail_'.get_client_ip());

        // Should be forbidden if sending email frequently
        Cache::put($lastMailCacheKey, time());
        $this->postJson('/auth/forgot', [
            'captcha' => 'a',
        ])->assertJson([
            'code' => 2,
            'message' => trans('auth.forgot.frequent-mail'),
        ]);
        Cache::flush();
        $this->flushSession();

        // Should return a warning if user is not existed
        $user = factory(User::class)->create();
        $this->withSession(['phrase' => 'a'])->postJson('/auth/forgot', [
            'email' => 'nope@nope.net',
            'captcha' => 'a',
        ])->assertJson([
            'code' => 1,
            'message' => trans('auth.forgot.unregistered'),
        ]);

        $this->postJson('/auth/forgot', [
            'email' => $user->email,
            'captcha' => 'a',
        ])->assertJson([
            'code' => 0,
            'message' => trans('auth.forgot.success'),
        ]);
        $this->assertTrue(Cache::has($lastMailCacheKey));
        Cache::flush();
        Mail::assertSent(ForgotPassword::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        // Should handle exception when sending email
        Mail::shouldReceive('to')
            ->once()
            ->andThrow(new \Mockery\Exception('A fake exception.'));
        $this->flushSession();
        $this->withSession(['phrase' => 'a'])
            ->postJson('/auth/forgot', [
                'email' => $user->email,
                'captcha' => 'a',
            ])->assertJson([
                'code' => 2,
                'message' => trans('auth.forgot.failed', ['msg' => 'A fake exception.']),
            ]);

        // Addition: Mailable test
        $site_name = option_localized('site_name');
        $mailable = new ForgotPassword('url');
        $mailable->build();
        $this->assertTrue($mailable->hasFrom(config('mail.username'), $site_name));
        $this->assertEquals(trans('auth.forgot.mail.title', ['sitename' => $site_name]), $mailable->subject);
        $this->assertEquals('mails.password-reset', $mailable->view);
    }

    public function testReset()
    {
        $user = factory(User::class)->create();

        $this->get(
            URL::temporarySignedRoute('auth.reset', now()->addHour(), ['uid' => $user->uid])
        )->assertSuccessful();
    }

    public function testHandleReset()
    {
        $user = factory(User::class)->create();
        $url = URL::temporarySignedRoute('auth.reset', now()->addHour(), ['uid' => $user->uid]);

        // Should return a warning if `password` is empty
        $this->postJson($url)->assertJsonValidationErrors('password');

        // Should return a warning if `password` is too short
        $this->postJson(
            $url, [
            'password' => '123',
        ])->assertJsonValidationErrors('password');

        // Should return a warning if `password` is too long
        $this->postJson(
            $url, [
            'password' => Str::random(33),
        ])->assertJsonValidationErrors('password');

        // Success
        $this->postJson(
            $url, [
            'password' => '12345678',
        ])->assertJson([
            'code' => 0,
            'message' => trans('auth.reset.success'),
        ]);
        // We must re-query the user model,
        // because the old instance hasn't been changed
        // after resetting password.
        $user = User::find($user->uid);
        $this->assertTrue($user->verifyPassword('12345678'));
    }

    public function testVerify()
    {
        $url = URL::signedRoute('auth.verify', ['uid' => 1]);

        // Should be forbidden if account verification is disabled
        option(['require_verification' => false]);
        $this->get($url)->assertSee(trans('user.verification.disabled'));
        option(['require_verification' => true]);

        $this->get($url)->assertSee(trans('auth.verify.invalid'));

        $user = factory(User::class)->create();
        $url = URL::signedRoute('auth.verify', ['uid' => $user->uid]);
        $this->get($url)->assertSee(trans('auth.verify.invalid'));

        $user = factory(User::class)->create(['verified' => false]);
        $url = URL::signedRoute('auth.verify', ['uid' => $user->uid]);
        $this->get($url)->assertViewIs('auth.verify');
        $this->assertEquals(1, User::find($user->uid)->verified);
    }

    public function testApiLogin()
    {
        $user = factory(User::class)->create();
        $user->changePassword('12345678');

        $this->postJson('/api/auth/login')->assertJson(['token' => false]);
        $token = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '12345678'
        ])->decodeResponseJson('token');
        $this->assertTrue(is_string($token));
    }

    public function testApiLogout()
    {
        $user = factory(User::class)->create();
        $user->changePassword('12345678');
        $token = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => '12345678'
        ])->decodeResponseJson('token');

        $this->post('/api/auth/logout', [], [
            'Authorization' => "Bearer $token"
        ])->assertStatus(204);
    }
}

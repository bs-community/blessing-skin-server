<?php

use App\Events;
use App\Models\User;
use App\Services\Facades\Option;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AuthControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testLogin()
    {
        $this->visit('/auth/login')->see('Log in');
    }

    public function testHandleLogin()
    {
        $this->expectsEvents(Events\UserTryToLogin::class);
        $this->expectsEvents(Events\UserLoggedIn::class);

        $user = factory(User::class)->create();
        $user->changePasswd('12345678');
        $player = factory(App\Models\Player::class)->create(
            [
                'uid' => $user->uid
            ]
        );

        // Should return a warning if `identification` is empty
        $this->post(
            '/auth/login', [], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('validation.required', ['attribute' => trans('auth.identification')])
        ]);

        // Should return a warning if `password` is empty
        $this->post(
            '/auth/login', [
            'identification' => $user->email
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('validation.required', ['attribute' => 'password'])
        ]);

        // Should return a warning if length of `password` is lower than 6
        $this->post(
            '/auth/login', [
            'identification' => $user->email,
            'password' => '123'
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('validation.min.string', ['attribute' => 'password', 'min' => 6])
        ]);

        // Should return a warning if length of `password` is greater than 32
        $this->post(
            '/auth/login', [
            'identification' => $user->email,
            'password' => str_random(80)
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('validation.max.string', ['attribute' => 'password', 'max' => 32])
        ]);

        $this->flushSession();

        // Logging in should be failed if password is wrong
        $this->post(
            '/auth/login', [
            'identification' => $user->email,
            'password' => 'wrong-password'
        ])->seeJson(
            [
                'errno' => 1,
                'msg' => trans('auth.validation.password'),
                'login_fails' => 1
            ]
        )->assertSessionHas('login_fails', 1);

        $this->flushSession();

        // Should check captcha if there are too many fails
        $this->withSession(
            [
                'login_fails' => 4,
                'phrase' => 'a'
            ]
        )->post(
            '/auth/login', [
            'identification' => $user->email,
            'password' => '12345678',
            'captcha' => 'b'
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('auth.validation.captcha')
        ]);

        $this->flushSession();

        // Should return a warning if user isn't existed
        $this->post(
            '/auth/login', [
            'identification' => 'nope@nope.net',
            'password' => '12345678'
        ])->seeJson([
            'errno' => 2,
            'msg' => trans('auth.validation.user')
        ]);

        $this->flushSession();

        // Should clean the `login_fails` session if logged in successfully
        $this->withSession(['login_fails' => 1])->post('/auth/login', [
            'identification' => $user->email,
            'password' => '12345678'
        ])->seeJson(
            [
                'errno' => 0,
                'msg' => trans('auth.login.success'),
                'token' => $user->getToken()
            ]
        )->assertSessionMissing('login_fails');

        $this->flushSession();

        // Logged in should be in success if logged in with player name
        $this->post(
            '/auth/login', [
            'identification' => $player->player_name,
            'password' => '12345678'
        ]
        )->seeJson(
            [
                'errno' => 0,
                'msg' => trans('auth.login.success'),
                'token' => $user->getToken()
            ]
        )->seeCookie('uid', $user->uid)
            ->seeCookie('token', $user->getToken())
            ->assertSessionHasAll(
                [
                    'uid' => $user->uid,
                    'token' => $user->getToken()
                ]
            );
    }

    public function testLogout()
    {
        $user = factory(User::class)->create();

        $this->withSession(
            [
                'uid' => $user->uid,
                'token' => $user->getToken()
            ]
        )->post('/auth/logout')->seeJson(
            [
                'errno' => 0,
                'msg' => trans('auth.logout.success')
            ]
        )->assertSessionMissing(['uid', 'token']);

        $this->flushSession();
        $this->post('/auth/logout')
            ->seeJson([
                'errno' => 1,
                'msg' => trans('auth.logout.fail')
            ]);
    }

    public function testRegister()
    {
        $this->visit('/auth/register')->see('Register');

        option(['user_can_register' => false]);
        $this->visit('/auth/register')->see(trans('auth.register.close'));
    }

    public function testHandleRegister()
    {
        $this->expectsEvents(Events\UserRegistered::class);

        // Should return a warning if `captcha` is wrong
        $this->withSession(['phrase' => 'a'])
            ->post(
                '/auth/register', [], [
                'X-Requested-With' => 'XMLHttpRequest'
            ])->seeJson([
                'errno' => 1,
                'msg' => trans('auth.validation.captcha')
            ]);

        // Once we have sent session in the last assertion,
        // we don't need to send it again until we flush it.
        // Should return a warning if `email` is empty
        $this->post(
            '/auth/register',
            ['captcha' => 'a'],
            ['X-Requested-With' => 'XMLHttpRequest']
        )->seeJson([
            'errno' => 1,
            'msg' => trans('validation.required', ['attribute' => 'email'])
        ]);

        // Should return a warning if `email` is invalid
        $this->post(
            '/auth/register',
            [
                'email' => 'not_an_email',
                'captcha' => 'a'
            ],
            ['X-Requested-With' => 'XMLHttpRequest']
        )->seeJson([
            'errno' => 1,
            'msg' => trans('validation.email', ['attribute' => 'email'])
        ]);

        // Should return a warning if `password` is empty
        $this->post(
            '/auth/register',
            [
                'email' => 'a@b.c',
                'captcha' => 'a'
            ],
            ['X-Requested-With' => 'XMLHttpRequest']
        )->seeJson([
            'errno' => 1,
            'msg' => trans('validation.required', ['attribute' => 'password'])
        ]);

        // Should return a warning if length of `password` is lower than 8
        $this->post(
            '/auth/register',
            [
                'email' => 'a@b.c',
                'password' => '1',
                'captcha' => 'a'
            ],
            ['X-Requested-With' => 'XMLHttpRequest']
        )->seeJson([
            'errno' => 1,
            'msg' => trans('validation.min.string', ['attribute' => 'password', 'min' => 8])
        ]);

        // Should return a warning if length of `password` is greater than 32
        $this->post(
            '/auth/register',
            [
                'email' => 'a@b.c',
                'password' => str_random(33),
                'captcha' => 'a'
            ],
            ['X-Requested-With' => 'XMLHttpRequest']
        )->seeJson([
            'errno' => 1,
            'msg' => trans('validation.max.string', ['attribute' => 'password', 'max' => 32])
        ]);

        // Should return a warning if `nickname` is empty
        $this->post(
            '/auth/register',
            [
                'email' => 'a@b.c',
                'password' => '12345678',
                'captcha' => 'a'
            ],
            ['X-Requested-With' => 'XMLHttpRequest']
        )->seeJson([
            'errno' => 1,
            'msg' => trans('validation.required', ['attribute' => 'nickname'])
        ]);

        // Should return a warning if `nickname` is invalid
        $this->post(
            '/auth/register',
            [
                'email' => 'a@b.c',
                'password' => '12345678',
                'nickname' => '\\',
                'captcha' => 'a'
            ],
            ['X-Requested-With' => 'XMLHttpRequest']
        )->seeJson([
            'errno' => 1,
            'msg' => trans('validation.nickname', ['attribute' => 'nickname'])
        ]);

        // Should return a warning if `nickname` is too long
        $this->post(
            '/auth/register',
            [
                'email' => 'a@b.c',
                'password' => '12345678',
                'nickname' => str_random(256),
                'captcha' => 'a'
            ],
            ['X-Requested-With' => 'XMLHttpRequest']
        )->seeJson([
            'errno' => 1,
            'msg' => trans('validation.max.string', ['attribute' => 'nickname', 'max' => 255])
        ]);

        // Should be forbidden if registering is closed
        Option::set('user_can_register', false);
        $this->post(
            '/auth/register',
            [
                'email' => 'a@b.c',
                'password' => '12345678',
                'nickname' => 'nickname',
                'captcha' => 'a'
            ],
            ['X-Requested-With' => 'XMLHttpRequest']
        )->seeJson([
            'errno' => 7,
            'msg' => trans('auth.register.close')
        ]);

        // Reopen for test
        Option::set('user_can_register', true);

        // Should be forbidden if registering's count current IP is over
        Option::set('regs_per_ip', -1);
        $this->post(
            '/auth/register',
            [
                'email' => 'a@b.c',
                'password' => '12345678',
                'nickname' => 'nickname',
                'captcha' => 'a'
            ]
        )->seeJson([
            'errno' => 7,
            'msg' => trans('auth.register.max', ['regs' => option('regs_per_ip')])
        ]);

        Option::set('regs_per_ip', 100);

        // Should return a warning if using a duplicated email
        $existedUser = factory(User::class)->create();
        $this->post(
            '/auth/register',
            [
                'email' => $existedUser->email,
                'password' => '12345678',
                'nickname' => 'nickname',
                'captcha' => 'a'
            ]
        )->seeJson([
            'errno' => 5,
            'msg' => trans('auth.register.registered')
        ]);

        // Database should be updated if succeeded
        $response = $this->post(
            '/auth/register',
            [
                'email' => 'a@b.c',
                'password' => '12345678',
                'nickname' => 'nickname',
                'captcha' => 'a'
            ]
        );
        $newUser = User::where('email', 'a@b.c')->first();
        $response->seeJson([
            'errno' => 0,
            'msg' => trans('auth.register.success'),
            'token' => $newUser->getToken()
        ])->seeCookie('uid', $newUser->uid)
            ->seeCookie('token', $newUser->getToken());
        $this->assertTrue($newUser->verifyPassword('12345678'));
        $this->seeInDatabase('users', [
            'email' => 'a@b.c',
            'nickname' => 'nickname',
            'score' => option('user_initial_score'),
            'ip' => '127.0.0.1',
            'permission' => User::NORMAL
        ]);
    }

    public function testForgot()
    {
        $this->visit('/auth/forgot')->see('Forgot Password');

        config(['mail.host' => '']);
        $this->visit('/auth/forgot')->see(trans('auth.forgot.close'));
    }

    public function testHandleForgot()
    {
        // Should return a warning if `captcha` is wrong
        $this->withSession(['phrase' => 'a'])->post('/auth/forgot', [
            'captcha' => 'b'
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('auth.validation.captcha')
        ]);

        // Should be forbidden if "forgot password" is closed
        config(['mail.host' => '']);
        $this->withSession(['phrase' => 'a'])->post('/auth/forgot', [
            'captcha' => 'a'
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('auth.forgot.close')
        ]);
        config(['mail.host' => 'localhost']);

        // Should be forbidden if sending email frequently
        $this->withSession(['last_mail_time' => time()])->post('/auth/forgot', [
            'captcha' => 'a'
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('auth.forgot.frequent-mail')
        ]);

        // Should return a warning if user is not existed
        $this->flushSession();
        $user = factory(User::class)->create();
        $this->withSession(['phrase' => 'a'])->post('/auth/forgot', [
            'email' => 'nope@nope.net',
            'captcha' => 'a'
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('auth.forgot.unregistered')
        ]);

        $uid = $user->uid;
        $token = base64_encode(
            $user->getToken().substr(time(), 4, 6).str_random(16)
        );
        $url = Option::get('site_url')."/auth/reset?uid=$uid&token=$token";
        // An email should be send
        // Laravel supports `Mail::fake()` since v5.4, but now we cannot
        // Thanks: https://stackoverflow.com/questions/31120567/unittesting-laravel-5-mail-using-mock
        Mail::shouldReceive('send')
            ->once()
            ->with(
                'auth.mail',
                \Mockery::on(function ($actual) use ($url) {
                    $this->assertEquals(0, stristr($url, $actual['reset_url']));
                    return true;
                }),
                \Mockery::on(function (\Closure $closure) use ($user) {
                    $mock = \Mockery::mock(Illuminate\Mail\Message::class);

                    $mock->shouldReceive('from')
                        ->once()
                        ->with(option('mail.username'), option('site_name'));

                    $mock->shouldReceive('to')
                        ->once()
                        ->with($user->email)
                        ->andReturnSelf();

                    $mock->shouldReceive('subject')
                        ->once()
                        ->with(trans('auth.mail.title', ['sitename' => option('site_name')]));
                    $closure($mock);
                    return true;
                })
            );
        $this->post('/auth/forgot', [
            'email' => $user->email,
            'captcha' => 'a'
        ])->seeJson([
            'errno' => 0,
            'msg' => trans('auth.mail.success')
        ])->assertSessionHas('last_mail_time');

        // Should handle exception when sending email
        Mail::shouldReceive('send')
            ->once()
            ->andThrow(new \Mockery\Exception('A fake exception.'));
        $this->flushSession();
        $this->withSession(['phrase' => 'a'])
            ->post('/auth/forgot', [
                'email' => $user->email,
                'captcha' => 'a'
            ])->seeJson([
                'errno' => 2,
                'msg' => trans('auth.mail.failed', ['msg' => 'A fake exception.'])
            ]);
    }

    public function testReset()
    {
        $user = factory(User::class)->create();

        // Should be redirected if `uid` or `token` is empty
        $this->visit('/auth/reset')
            ->seePageIs('/auth/login')
            ->see(trans('auth.check.anonymous'));

        // Should be redirected if `uid` is invalid
        $this->visit('/auth/reset?uid=-1&token=nothing')
            ->seePageIs('/auth/forgot')
            ->see(trans('auth.reset.invalid'));

        // Should be redirected if `token` is invalid
        $this->visit('/auth/reset?uid=' . $user->uid . '&token=nothing')
            ->seePageIs('/auth/forgot')
            ->see(trans('auth.reset.invalid'));

        // Should be redirected if expired
        $token = base64_encode(
            $user->getToken().substr(time() - 60 * 60 * 2, 4, 6).str_random(16)
        );
        $this->visit('/auth/reset?uid=' . $user->uid . '&token=' . $token)
            ->seePageIs('/auth/forgot')
            ->see(trans('auth.reset.expired'));

        // Success
        $token = base64_encode(
            $user->getToken().substr(time(), 4, 6).str_random(16)
        );
        $uri = $this->visit('/auth/reset?uid=' . $user->uid . '&token=' . $token)
                    ->currentUri;
        $this->assertContains('/auth/reset', $uri);
    }

    public function testHandleReset()
    {
        $user = factory(User::class)->create();

        // Should return a warning if `uid` is empty
        $this->post('/auth/reset', [], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('validation.required', ['attribute' => 'uid'])
        ]);

        // Should return a warning if `uid` is not an integer
        $this->post('/auth/reset', [
            'uid' => 'string'
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('validation.integer', ['attribute' => 'uid'])
        ]);

        // Should return a warning if `password` is empty
        $this->post(
            '/auth/reset', [
            'uid' => $user->uid
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('validation.required', ['attribute' => 'password'])
        ]);

        // Should return a warning if `password` is too short
        $this->post(
            '/auth/reset', [
            'uid' => $user->uid,
            'password' => '123'
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('validation.min.string', ['attribute' => 'password', 'min' => 8])
        ]);

        // Should return a warning if `password` is too long
        $this->post(
            '/auth/reset', [
            'uid' => $user->uid,
            'password' => str_random(33)
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('validation.max.string', ['attribute' => 'password', 'max' => 32])
        ]);

        // Should be forbidden if `token` is missing
        $this->post(
            '/auth/reset', [
            'uid' => $user->uid,
            'password' => '12345678'
        ], ['X-Requested-With' => 'XMLHttpRequest'])->seeJson([
            'errno' => 1,
            'msg' => trans('validation.required', ['attribute' => 'token'])
        ]);

        // Should be forbidden if expired
        $token = base64_encode(
            $user->getToken().substr(time() - 60 * 60 * 2, 4, 6).str_random(16)
        );
        $this->post(
            '/auth/reset', [
            'uid' => $user->uid,
            'password' => '12345678',
            'token' => $token
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('auth.reset.expired')
        ]);

        // Should return a warning if the user is not existed
        $token = base64_encode(
            $user->getToken().substr(time(), 4, 6).str_random(16)
        );
        $this->post(
            '/auth/reset', [
            'uid' => -1,
            'password' => '12345678',
            'token' => $token
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('auth.reset.invalid')
        ]);

        // Should be forbidden if `token` is invalid
        $this->post(
            '/auth/reset', [
            'uid' => $user->uid,
            'password' => '12345678',
            'token' => 'invalid'
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('auth.reset.invalid')
        ]);

        // Success
        $token = base64_encode(
            $user->getToken().substr(time(), 4, 6).str_random(16)
        );
        $this->post(
            '/auth/reset', [
            'uid' => $user->uid,
            'password' => '12345678',
            'token' => $token
        ])->seeJson([
            'errno' => 0,
            'msg' => trans('auth.reset.success')
        ]);
        // We must re-query the user model,
        // because the old instance hasn't been changed
        // after resetting password.
        $user = User::find($user->uid);
        $this->assertTrue($user->verifyPassword('12345678'));
    }

    public function testCaptcha()
    {
        if (!function_exists('imagettfbbox') || getenv('TRAVIS_PHP_VERSION' == '5.5')) {
            $this->markTestSkipped('There are some problems with PHP 5.5 on Travis CI');
        }

        $this->get('/auth/captcha')
            ->assertResponseStatus(200)
            ->seeHeader('Content-Type', 'image/png')
            ->assertSessionHas('phrase');
    }
}

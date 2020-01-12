<?php

namespace App\Http\Controllers;

use App\Events;
use App\Exceptions\PrettyPageException;
use App\Mail\ForgotPassword;
use App\Models\Player;
use App\Models\User;
use App\Rules;
use Auth;
use Cache;
use Carbon\Carbon;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Mail;
use Session;
use URL;
use Vectorface\Whip\Whip;
use View;

class AuthController extends Controller
{
    public function login()
    {
        $whip = new Whip();
        $ip = $whip->getValidIpAddress();

        return view('auth.login', [
            'extra' => [
                'tooManyFails' => cache(sha1('login_fails_'.$ip)) > 3,
                'recaptcha' => option('recaptcha_sitekey'),
                'invisible' => (bool) option('recaptcha_invisible'),
            ],
        ]);
    }

    public function handleLogin(
        Request $request,
        Rules\Captcha $captcha,
        Dispatcher $dispatcher
    ) {
        $this->validate($request, [
            'identification' => 'required',
            'password' => 'required|min:6|max:32',
        ]);

        $identification = $request->input('identification');
        $password = $request->input('password');
        // Guess type of identification
        $authType = filter_var($identification, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $dispatcher->dispatch('auth.login.attempt', [$identification, $password, $authType]);
        event(new Events\UserTryToLogin($identification, $authType));

        if ($authType == 'email') {
            $user = User::where('email', $identification)->first();
        } else {
            $player = Player::where('name', $identification)->first();
            $user = optional($player)->user;
        }

        // Require CAPTCHA if user fails to login more than 3 times
        $whip = new Whip();
        $ip = $whip->getValidIpAddress();
        $loginFailsCacheKey = sha1('login_fails_'.$ip);
        $loginFails = (int) Cache::get($loginFailsCacheKey, 0);

        if ($loginFails > 3) {
            $this->validate($request, ['captcha' => ['required', $captcha]]);
        }

        if (!$user) {
            return json(trans('auth.validation.user'), 2);
        }

        $dispatcher->dispatch('auth.login.ready', [$user]);

        if ($user->verifyPassword($request->input('password'))) {
            Session::forget('login_fails');
            Cache::forget($loginFailsCacheKey);

            Auth::login($user, $request->input('keep'));

            $dispatcher->dispatch('auth.login.succeeded', [$user]);
            event(new Events\UserLoggedIn($user));

            return json(trans('auth.login.success'), 0, [
                'redirectTo' => $request->session()->pull('last_requested_path', url('/user')),
            ]);
        } else {
            $loginFails++;
            Cache::put($loginFailsCacheKey, $loginFails, 3600);
            $dispatcher->dispatch('auth.login.failed', [$user, $loginFails]);

            return json(trans('auth.validation.password'), 1, [
                'login_fails' => $loginFails,
            ]);
        }
    }

    public function logout(Dispatcher $dispatcher)
    {
        $user = Auth::user();

        $dispatcher->dispatch('auth.logout.before', [$user]);
        Auth::logout();
        $dispatcher->dispatch('auth.logout.after', [$user]);

        return json(trans('auth.logout.success'), 0);
    }

    public function register()
    {
        if (option('user_can_register')) {
            return view('auth.register', [
                'site_name' => option_localized('site_name'),
                'extra' => [
                    'player' => (bool) option('register_with_player_name'),
                    'recaptcha' => option('recaptcha_sitekey'),
                    'invisible' => (bool) option('recaptcha_invisible'),
                ],
            ]);
        } else {
            throw new PrettyPageException(trans('auth.register.close'), 7);
        }
    }

    public function handleRegister(
        Request $request,
        Rules\Captcha $captcha,
        Dispatcher $dispatcher
    ) {
        if (!option('user_can_register')) {
            return json(trans('auth.register.close'), 7);
        }

        $rule = option('register_with_player_name') ?
            ['player_name' => [
                'required',
                new Rules\PlayerName(),
                'min:'.option('player_name_length_min'),
                'max:'.option('player_name_length_max'),
            ]] :
            ['nickname' => 'required|max:255'];
        $data = $this->validate($request, array_merge([
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|max:32',
            'captcha' => ['required', $captcha],
        ], $rule));

        $dispatcher->dispatch('auth.registration.attempt', [$data]);

        if (
            option('register_with_player_name') &&
            Player::where('name', $request->input('player_name'))->count() > 0
        ) {
            return json(trans('user.player.add.repeated'), 2);
        }

        // If amount of registered accounts of IP is more than allowed amounts,
        // then reject the register.
        $whip = new Whip();
        $ip = $whip->getValidIpAddress();
        if (User::where('ip', $ip)->count() >= option('regs_per_ip')) {
            return json(trans('auth.register.max', ['regs' => option('regs_per_ip')]), 7);
        }

        $dispatcher->dispatch('auth.registration.ready', [$data]);

        $user = new User();
        $user->email = $data['email'];
        $user->nickname = $data[option('register_with_player_name') ? 'player_name' : 'nickname'];
        $user->score = option('user_initial_score');
        $user->avatar = 0;
        $user->password = $user->getEncryptedPwdFromEvent($data['password'])
            ?: app('cipher')->hash($data['password'], config('secure.salt'));
        $user->ip = $ip;
        $user->permission = User::NORMAL;
        $user->register_at = Carbon::now();
        $user->last_sign_at = Carbon::now()->subDay();

        $user->save();

        $dispatcher->dispatch('auth.registration.completed', [$user]);
        event(new Events\UserRegistered($user));

        if (option('register_with_player_name')) {
            $player = new Player();
            $player->uid = $user->uid;
            $player->name = $request->get('player_name');
            $player->tid_skin = 0;
            $player->save();

            event(new Events\PlayerWasAdded($player));
        }

        $dispatcher->dispatch('auth.login.ready', [$user]);
        Auth::login($user);
        $dispatcher->dispatch('auth.login.succeeded', [$user]);

        return json(trans('auth.register.success'), 0);
    }

    public function forgot()
    {
        if (config('mail.driver') != '') {
            return view('auth.forgot', [
                'extra' => [
                    'recaptcha' => option('recaptcha_sitekey'),
                    'invisible' => (bool) option('recaptcha_invisible'),
                ],
            ]);
        } else {
            throw new PrettyPageException(trans('auth.forgot.disabled'), 8);
        }
    }

    public function handleForgot(
        Request $request,
        Rules\Captcha $captcha,
        Dispatcher $dispatcher
    ) {
        $data = $this->validate($request, [
            'email' => 'required|email',
            'captcha' => ['required', $captcha],
        ]);

        if (!config('mail.driver')) {
            return json(trans('auth.forgot.disabled'), 1);
        }

        $email = $data['email'];
        $dispatcher->dispatch('auth.forgot.attempt', [$email]);

        $rateLimit = 180;
        $whip = new Whip();
        $ip = $whip->getValidIpAddress();
        $lastMailCacheKey = sha1('last_mail_'.$ip);
        $remain = $rateLimit + Cache::get($lastMailCacheKey, 0) - time();
        if ($remain > 0) {
            return json(trans('auth.forgot.frequent-mail'), 2);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return json(trans('auth.forgot.unregistered'), 1);
        }

        $dispatcher->dispatch('auth.forgot.ready', [$user]);

        $url = URL::temporarySignedRoute('auth.reset', now()->addHour(), ['uid' => $user->uid]);
        try {
            Mail::to($email)->send(new ForgotPassword($url));
        } catch (\Exception $e) {
            report($e);
            $dispatcher->dispatch('auth.forgot.failed', [$user, $url]);

            return json(trans('auth.forgot.failed', ['msg' => $e->getMessage()]), 2);
        }

        $dispatcher->dispatch('auth.forgot.sent', [$user, $url]);
        Cache::put($lastMailCacheKey, time(), 3600);

        return json(trans('auth.forgot.success'), 0);
    }

    public function reset($uid)
    {
        return view('auth.reset')->with('user', User::find($uid));
    }

    public function handleReset(Dispatcher $dispatcher, Request $request, $uid)
    {
        ['password' => $password] = $this->validate($request, [
            'password' => 'required|min:8|max:32',
        ]);
        $user = User::find($uid);

        $dispatcher->dispatch('auth.reset.before', [$user, $password]);
        $user->changePassword($password);
        $dispatcher->dispatch('auth.reset.after', [$user, $password]);

        return json(trans('auth.reset.success'), 0);
    }

    public function captcha(\Gregwar\Captcha\CaptchaBuilder $builder)
    {
        $builder->build(100, 34);
        session(['captcha' => $builder->getPhrase()]);

        return response($builder->output(), 200, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'no-store',
        ]);
    }

    public function fillEmail(Request $request)
    {
        $email = $this->validate($request, ['email' => 'required|email|unique:users'])['email'];
        $user = $request->user();
        $user->email = $email;
        $user->save();

        return redirect('/user');
    }

    public function verify($uid)
    {
        if (!option('require_verification')) {
            throw new PrettyPageException(trans('user.verification.disabled'), 1);
        }

        $user = User::find($uid);

        if (!$user || $user->verified) {
            throw new PrettyPageException(trans('auth.verify.invalid'), 1);
        }

        $user->verified = true;
        $user->save();

        return view('auth.verify', ['site_name' => option_localized('site_name')]);
    }

    public function jwtLogin(Request $request)
    {
        $token = Auth::guard('jwt')->attempt([
            'email' => $request->email,
            'password' => $request->password,
        ]) ?: '';

        return json(compact('token'));
    }

    public function jwtLogout()
    {
        Auth::guard('jwt')->logout();

        return response('', 204);
    }

    public function jwtRefresh()
    {
        return json(['token' => Auth::guard('jwt')->refresh()]);
    }

    public function oauthLogin($driver)
    {
        return Socialite::driver($driver)->redirect();
    }

    public function oauthCallback(Dispatcher $dispatcher, $driver)
    {
        $remoteUser = Socialite::driver($driver)->user();

        $email = $remoteUser->email;
        if (empty($email)) {
            abort(500, 'Unsupported OAuth Server which does not provide email.');
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            $whip = new Whip();
            $ip = $whip->getValidIpAddress();

            $user = new User();
            $user->email = $email;
            $user->nickname = $remoteUser->nickname ?? $remoteUser->name ?? $email;
            $user->score = option('user_initial_score');
            $user->avatar = 0;
            $user->password = '';
            $user->ip = $ip;
            $user->permission = User::NORMAL;
            $user->register_at = Carbon::now();
            $user->last_sign_at = Carbon::now()->subDay();
            $user->verified = true;

            $user->save();
            $dispatcher->dispatch('auth.registration.completed', [$user]);
        }

        $dispatcher->dispatch('auth.login.ready', [$user]);
        Auth::login($user);
        $dispatcher->dispatch('auth.login.succeeded', [$user]);

        return redirect('/user');
    }
}

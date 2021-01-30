<?php

namespace App\Http\Controllers;

use App\Events;
use App\Exceptions\PrettyPageException;
use App\Mail\ForgotPassword;
use App\Models\Player;
use App\Models\User;
use App\Rules;
use Auth;
use Blessing\Filter;
use Blessing\Rejection;
use Cache;
use Carbon\Carbon;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use Mail;
use Session;
use URL;
use Vectorface\Whip\Whip;

class AuthController extends Controller
{
    public function login(Filter $filter)
    {
        $whip = new Whip();
        $ip = $whip->getValidIpAddress();
        $ip = $filter->apply('client_ip', $ip);

        $rows = [
            'auth.rows.login.notice',
            'auth.rows.login.message',
            'auth.rows.login.form',
            'auth.rows.login.registration-link',
        ];
        $rows = $filter->apply('auth_page_rows:login', $rows);

        return view('auth.login', [
            'rows' => $rows,
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
        Dispatcher $dispatcher,
        Filter $filter
    ) {
        $data = $request->validate([
            'identification' => 'required',
            'password' => 'required|min:6|max:32',
        ]);
        $identification = $data['identification'];
        $password = $data['password'];

        $can = $filter->apply('can_login', null, [$identification, $password]);
        if ($can instanceof Rejection) {
            return json($can->getReason(), 1);
        }

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
        $ip = $filter->apply('client_ip', $ip);
        $loginFailsCacheKey = sha1('login_fails_'.$ip);
        $loginFails = (int) Cache::get($loginFailsCacheKey, 0);

        if ($loginFails > 3) {
            $request->validate(['captcha' => ['required', $captcha]]);
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

    public function register(Filter $filter)
    {
        $rows = [
            'auth.rows.register.notice',
            'auth.rows.register.form',
        ];
        $rows = $filter->apply('auth_page_rows:register', $rows);

        return view('auth.register', [
            'site_name' => option_localized('site_name'),
            'rows' => $rows,
            'extra' => [
                'player' => (bool) option('register_with_player_name'),
                'recaptcha' => option('recaptcha_sitekey'),
                'invisible' => (bool) option('recaptcha_invisible'),
            ],
        ]);
    }

    public function handleRegister(
        Request $request,
        Rules\Captcha $captcha,
        Dispatcher $dispatcher,
        Filter $filter
    ) {
        $can = $filter->apply('can_register', null);
        if ($can instanceof Rejection) {
            return json($can->getReason(), 1);
        }

        $rule = option('register_with_player_name') ?
            ['player_name' => [
                'required',
                new Rules\PlayerName(),
                'min:'.option('player_name_length_min'),
                'max:'.option('player_name_length_max'),
            ]] :
            ['nickname' => 'required|max:255'];
        $data = $request->validate(array_merge([
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|max:32',
            'captcha' => ['required', $captcha],
        ], $rule));
        $playerName = $request->input('player_name');

        $dispatcher->dispatch('auth.registration.attempt', [$data]);

        if (
            option('register_with_player_name') &&
            Player::where('name', $playerName)->count() > 0
        ) {
            return json(trans('user.player.add.repeated'), 1);
        }

        // If amount of registered accounts of IP is more than allowed amount,
        // reject this registration.
        $whip = new Whip();
        $ip = $whip->getValidIpAddress();
        $ip = $filter->apply('client_ip', $ip);
        if (User::where('ip', $ip)->count() >= option('regs_per_ip')) {
            return json(trans('auth.register.max', ['regs' => option('regs_per_ip')]), 1);
        }

        $dispatcher->dispatch('auth.registration.ready', [$data]);

        $user = new User();
        $user->email = $data['email'];
        $user->nickname = $data[option('register_with_player_name') ? 'player_name' : 'nickname'];
        $user->score = option('user_initial_score');
        $user->avatar = 0;
        $password = app('cipher')->hash($data['password'], config('secure.salt'));
        $password = $filter->apply('user_password', $password);
        $user->password = $password;
        $user->ip = $ip;
        $user->permission = User::NORMAL;
        $user->register_at = Carbon::now();
        $user->last_sign_at = Carbon::now()->subDay();
        $user->save();

        $dispatcher->dispatch('auth.registration.completed', [$user]);
        event(new Events\UserRegistered($user));

        if (option('register_with_player_name')) {
            $dispatcher->dispatch('player.adding', [$playerName, $user]);

            $player = new Player();
            $player->uid = $user->uid;
            $player->name = $playerName;
            $player->tid_skin = 0;
            $player->save();

            $dispatcher->dispatch('player.added', [$player, $user]);
            event(new Events\PlayerWasAdded($player));
        }

        $dispatcher->dispatch('auth.login.ready', [$user]);
        Auth::login($user);
        $dispatcher->dispatch('auth.login.succeeded', [$user]);

        return json(trans('auth.register.success'), 0);
    }

    public function forgot()
    {
        if (config('mail.default') != '') {
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
        Dispatcher $dispatcher,
        Filter $filter
    ) {
        $data = $request->validate([
            'email' => 'required|email',
            'captcha' => ['required', $captcha],
        ]);

        if (!config('mail.default')) {
            return json(trans('auth.forgot.disabled'), 1);
        }

        $email = $data['email'];
        $dispatcher->dispatch('auth.forgot.attempt', [$email]);

        $rateLimit = 180;
        $whip = new Whip();
        $ip = $whip->getValidIpAddress();
        $ip = $filter->apply('client_ip', $ip);
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

        $url = URL::temporarySignedRoute(
            'auth.reset',
            Carbon::now()->addHour(),
            ['uid' => $user->uid],
            false
        );
        try {
            Mail::to($email)->send(new ForgotPassword(url($url)));
        } catch (\Exception $e) {
            report($e);
            $dispatcher->dispatch('auth.forgot.failed', [$user, $url]);

            return json(trans('auth.forgot.failed', ['msg' => $e->getMessage()]), 2);
        }

        $dispatcher->dispatch('auth.forgot.sent', [$user, $url]);
        Cache::put($lastMailCacheKey, time(), 3600);

        return json(trans('auth.forgot.success'), 0);
    }

    public function reset(Request $request, $uid)
    {
        abort_unless($request->hasValidSignature(false), 403, trans('auth.reset.invalid'));

        return view('auth.reset')->with('user', User::find($uid));
    }

    public function handleReset(Dispatcher $dispatcher, Request $request, $uid)
    {
        abort_unless($request->hasValidSignature(false), 403, trans('auth.reset.invalid'));

        ['password' => $password] = $request->validate([
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
        $email = $request->validate(['email' => 'required|email|unique:users'])['email'];
        $user = $request->user();
        $user->email = $email;
        $user->save();

        return redirect('/user');
    }

    public function verify(Request $request)
    {
        if (!option('require_verification')) {
            throw new PrettyPageException(trans('user.verification.disabled'), 1);
        }

        abort_unless($request->hasValidSignature(false), 403, trans('auth.verify.invalid'));

        return view('auth.verify');
    }

    public function handleVerify(Request $request, User $user)
    {
        abort_unless($request->hasValidSignature(false), 403, trans('auth.verify.invalid'));

        ['email' => $email] = $request->validate(['email' => 'required|email']);

        if ($user->email !== $email) {
            return back()->with('errorMessage', trans('auth.verify.not-matched'));
        }

        $user->verified = true;
        $user->save();

        return redirect()->route('user.home');
    }
}

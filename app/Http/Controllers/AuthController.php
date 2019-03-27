<?php

namespace App\Http\Controllers;

use URL;
use Mail;
use View;
use Cache;
use Session;
use App\Events;
use App\Models\User;
use App\Models\Player;
use App\Rules\Captcha;
use App\Mail\ForgotPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\PrettyPageException;

class AuthController extends Controller
{
    public function handleLogin(Request $request)
    {
        $this->validate($request, [
            'identification' => 'required',
            'password'       => 'required|min:6|max:32',
        ]);

        $identification = $request->input('identification');

        // Guess type of identification
        $authType = filter_var($identification, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        event(new Events\UserTryToLogin($identification, $authType));

        if ($authType == 'email') {
            $user = User::where('email', $identification)->first();
        } else {
            $player = Player::where('name', $identification)->first();
            $user = $player ? $player->user : null;
        }

        // Require CAPTCHA if user fails to login more than 3 times
        $loginFailsCacheKey = sha1('login_fails_'.get_client_ip());
        $loginFails = (int) Cache::get($loginFailsCacheKey, 0);

        if ($loginFails > 3) {
            $this->validate($request, ['captcha' => ['required', new Captcha]]);
        }

        if (! $user) {
            return json(trans('auth.validation.user'), 2);
        } else {
            if ($user->verifyPassword($request->input('password'))) {
                Session::forget('login_fails');

                Auth::login($user, $request->input('keep') == 'true');

                event(new Events\UserLoggedIn($user));

                Cache::forget($loginFailsCacheKey);

                return json(trans('auth.login.success'), 0);
            } else {
                // Increase the counter
                Cache::put($loginFailsCacheKey, ++$loginFails, 3600);

                return json(trans('auth.validation.password'), 1, [
                    'login_fails' => $loginFails,
                ]);
            }
        }
    }

    public function logout()
    {
        if (Auth::check()) {
            Auth::logout();
            return json(trans('auth.logout.success'), 0);
        } else {
            return json(trans('auth.logout.fail'), 1);
        }
    }

    public function register()
    {
        if (option('user_can_register')) {
            return view('auth.register', [
                'extra' => [
                    'player' => option('register_with_player_name'),
                    'recaptcha' => option('recaptcha_sitekey'),
                    'invisible' => (bool) option('recaptcha_invisible'),
                ]
            ]);
        } else {
            throw new PrettyPageException(trans('auth.register.close'), 7);
        }
    }

    public function handleRegister(Request $request)
    {
        if (! option('user_can_register')) {
            return json(trans('auth.register.close'), 7);
        }

        $rule = option('register_with_player_name') ?
            ['player_name' => 'required|player_name|min:'.option('player_name_length_min').'|max:'.option('player_name_length_max')] :
            ['nickname' => 'required|no_special_chars|max:255'];
        $data = $this->validate($request, array_merge([
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8|max:32',
            'captcha'  => ['required', new Captcha],
        ], $rule));

        if (option('register_with_player_name')) {
            event(new Events\CheckPlayerExists($request->get('player_name')));

            if (Player::where('name', $request->get('player_name'))->first()) {
                return json(trans('user.player.add.repeated'), 2);
            }
        }

        // If amount of registered accounts of IP is more than allowed amounts,
        // then reject the register.
        if (User::where('ip', get_client_ip())->count() >= option('regs_per_ip')) {
            return json(trans('auth.register.max', ['regs' => option('regs_per_ip')]), 7);
        }

        $user = new User;
        $user->email = $data['email'];
        $user->nickname = $data[option('register_with_player_name') ? 'player_name' : 'nickname'];
        $user->score = option('user_initial_score');
        $user->avatar = 0;
        $user->password = User::getEncryptedPwdFromEvent($data['password'], $user)
            ?: app('cipher')->hash($data['password'], config('secure.salt'));
        $user->ip = get_client_ip();
        $user->permission = User::NORMAL;
        $user->register_at = get_datetime_string();
        $user->last_sign_at = get_datetime_string(time() - 86400);

        $user->save();

        event(new Events\UserRegistered($user));

        if (option('register_with_player_name')) {
            $player = new Player;
            $player->uid = $user->uid;
            $player->name = $request->get('player_name');
            $player->tid_skin = 0;
            $player->save();

            event(new Events\PlayerWasAdded($player));
        }

        Auth::login($user);

        return json([
            'errno' => 0,
            'msg' => trans('auth.register.success'),
        ]);
    }

    public function forgot()
    {
        if (config('mail.driver') != '') {
            return view('auth.forgot', [
                'extra' => [
                    'recaptcha' => option('recaptcha_sitekey'),
                    'invisible' => (bool) option('recaptcha_invisible'),
                ]
            ]);
        } else {
            throw new PrettyPageException(trans('auth.forgot.disabled'), 8);
        }
    }

    public function handleForgot(Request $request)
    {
        $this->validate($request, [
            'captcha' => ['required', new Captcha],
        ]);

        if (! config('mail.driver')) {
            return json(trans('auth.forgot.disabled'), 1);
        }

        $rateLimit = 180;
        $lastMailCacheKey = sha1('last_mail_'.get_client_ip());
        $remain = $rateLimit + Cache::get($lastMailCacheKey, 0) - time();

        // Rate limit
        if ($remain > 0) {
            return json([
                'errno' => 2,
                'msg' => trans('auth.forgot.frequent-mail'),
                'remain' => $remain,
            ]);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return json(trans('auth.forgot.unregistered'), 1);
        }

        $url = URL::temporarySignedRoute('auth.reset', now()->addHour(), ['uid' => $user->uid]);

        try {
            Mail::to($request->input('email'))->send(new ForgotPassword($url));
        } catch (\Exception $e) {
            report($e);

            return json(trans('auth.forgot.failed', ['msg' => $e->getMessage()]), 2);
        }

        Cache::put($lastMailCacheKey, time(), 3600);

        return json(trans('auth.forgot.success'), 0);
    }

    public function reset($uid)
    {
        return view('auth.reset')->with('user', User::find($uid));
    }

    public function handleReset($uid, Request $request)
    {
        $validated = $this->validate($request, [
            'password' => 'required|min:8|max:32',
        ]);

        User::find($uid)->changePassword($validated['password']);

        return json(trans('auth.reset.success'), 0);
    }

    public function verify($uid)
    {
        if (! option('require_verification')) {
            throw new PrettyPageException(trans('user.verification.disabled'), 1);
        }

        $user = User::find($uid);

        if (! $user || $user->verified) {
            throw new PrettyPageException(trans('auth.verify.invalid'), 1);
        }

        $user->verified = true;
        $user->save();

        return view('auth.verify');
    }
}

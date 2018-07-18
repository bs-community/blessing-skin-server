<?php

namespace App\Http\Controllers;

use URL;
use Log;
use Mail;
use View;
use Utils;
use Cookie;
use Option;
use Session;
use App\Events;
use App\Models\User;
use App\Mail\ForgotPassword;
use Illuminate\Http\Request;
use App\Exceptions\PrettyPageException;
use App\Services\Repositories\UserRepository;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function handleLogin(Request $request, UserRepository $users)
    {
        $this->validate($request, [
            'identification' => 'required',
            'password'       => 'required|min:6|max:32'
        ]);

        $identification = $request->input('identification');

        // Guess type of identification
        $authType = (validate($identification, 'email')) ? "email" : "username";

        event(new Events\UserTryToLogin($identification, $authType));

        // Get user instance from repository.
        // If the given identification is not registered yet,
        // it will return a null value.
        $user = $users->get($identification, $authType);

        if (session('login_fails', 0) > 3) {
            if (strtolower($request->input('captcha')) != strtolower(session('phrase')))
                return json(trans('auth.validation.captcha'), 1);
        }

        if (! $user) {
            return json(trans('auth.validation.user'), 2);
        } else {
            if ($user->verifyPassword($request->input('password'))) {
                Session::forget('login_fails');

                Session::put('uid'  , $user->uid);
                Session::put('token', $user->getToken());

                // Time in minutes
                $time = $request->input('keep') == true ? 10080 : 60;

                event(new Events\UserLoggedIn($user));

                session()->forget('last_requested_path');

                return json(trans('auth.login.success'), 0, [
                    'token' => $user->getToken()
                ]) // Set cookies
                ->withCookie('uid', $user->uid, $time)
                ->withCookie('token', $user->getToken(), $time);
            } else {
                Session::put('login_fails', session('login_fails', 0) + 1);

                return json(trans('auth.validation.password'), 1, [
                    'login_fails' => session('login_fails')
                ]);
            }
        }
    }

    public function logout(Request $request)
    {
        if (Session::has('uid') && Session::has('token')) {
            // Flush sessions
            Session::flush();

            // Delete cookies
            return json(trans('auth.logout.success'), 0)
                    ->withCookie(Cookie::forget('uid'))
                    ->withCookie(Cookie::forget('token'));
        } else {
            return json(trans('auth.logout.fail'), 1);
        }
    }

    public function register()
    {
        if (option('user_can_register')) {
            return view('auth.register');
        } else {
            throw new PrettyPageException(trans('auth.register.close'), 7);
        }
    }

    public function handleRegister(Request $request, UserRepository $users)
    {
        if (! $this->checkCaptcha($request))
            return json(trans('auth.validation.captcha'), 1);

        $this->validate($request, [
            'email'    => 'required|email',
            'password' => 'required|min:8|max:32',
            'nickname' => 'required|no_special_chars|max:255'
        ]);

        if (! option('user_can_register')) {
            return json(trans('auth.register.close'), 7);
        }

        // If amount of registered accounts of IP is more than allowed amounts,
        // then reject the register.
        if (User::where('ip', Utils::getClientIp())->count() < option('regs_per_ip'))
        {
            // Register a new user.
            // If the email is already registered,
            // it will return a false value.
            $user = User::register(
                $request->input('email'),
                $request->input('password'), function($user) use ($request)
            {
                $user->ip           = Utils::getClientIp();
                $user->score        = option('user_initial_score');
                $user->register_at  = Utils::getTimeFormatted();
                $user->last_sign_at = Utils::getTimeFormatted(time() - 86400);
                $user->permission   = User::NORMAL;
                $user->nickname     = $request->input('nickname');
            });

            if (! $user) {
                return json(trans('auth.register.registered'), 5);
            }

            event(new Events\UserRegistered($user));

            return json([
                'errno'    => 0,
                'msg'      => trans('auth.register.success'),
                'token'    => $user->getToken(),
            ]) // Set cookies
            ->withCookie('uid', $user->uid, 60)
            ->withCookie('token', $user->getToken(), 60);

        } else {
            return json(trans('auth.register.max', ['regs' => option('regs_per_ip')]), 7);
        }
    }

    public function forgot()
    {
        if (config('mail.driver') != "") {
            return view('auth.forgot');
        } else {
            throw new PrettyPageException(trans('auth.forgot.close'), 8);
        }
    }

    public function handleForgot(Request $request, UserRepository $users)
    {
        if (! $this->checkCaptcha($request))
            return json(trans('auth.validation.captcha'), 1);

        if (config('mail.driver') == "")
            return json(trans('auth.forgot.close'), 1);

        if (Session::has('last_mail_time') && (time() - session('last_mail_time')) < 60)
            return json(trans('auth.forgot.frequent-mail'), 1);

        // Get user instance
        $user = $users->get($request->input('email'), 'email');

        if (! $user)
            return json(trans('auth.forgot.unregistered'), 1);

        $url = URL::temporarySignedRoute('auth.reset', now()->addHour(), ['uid' => $user->uid]);

        try {
            Mail::to($request->input('email'))->send(new ForgotPassword($url));
        } catch (\Exception $e) {
            // Write the exception to log
            app(\Illuminate\Foundation\Exceptions\Handler::class)->report($e);

            return json(trans('auth.mail.failed', ['msg' => $e->getMessage()]), 2);
        }

        Session::put('last_mail_time', time());

        return json(trans('auth.mail.success'), 0);
    }

    public function reset($uid, UserRepository $users)
    {
        return view('auth.reset')->with('user', $users->get($uid));
    }

    public function handleReset($uid, Request $request, UserRepository $users)
    {
        $validated = $this->validate($request, [
            'password' => 'required|min:8|max:32',
        ]);

        $users->get($uid)->changePasswd($validated['password']);

        return json(trans('auth.reset.success'), 0);
    }

    public function captcha()
    {
        $builder = new \Gregwar\Captcha\CaptchaBuilder;
        $builder->build($width = 100, $height = 34);
        Session::put('phrase', $builder->getPhrase());

        ob_start();
        $builder->output();
        $captcha = ob_get_contents();
        ob_end_clean();

        return \Response::png($captcha);
    }

    protected function checkCaptcha($request)
    {
        return (strtolower($request->input('captcha')) == strtolower(session('phrase')));
    }

}

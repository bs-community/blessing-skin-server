<?php

namespace App\Http\Controllers;

use Mail;
use View;
use Utils;
use Option;
use Session;
use App\Models\User;
use App\Models\UserModel;
use Illuminate\Http\Request;
use App\Exceptions\PrettyPageException;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function handleLogin(Request $request)
    {
        $this->validate($request, [
            'identification' => 'required',
            'password'       => 'required|min:6|max:16'
        ]);

        $identification = $request->input('identification');

        $auth_type = (validate($request->input('identification'), 'email')) ? "email" : "username";

        // instantiate user
        $user = new User(null, [$auth_type => $identification]);

        if (session('login_fails', 0) > 3) {
            if (strtolower($request->input('captcha')) != strtolower(session('phrase')))
                return json(trans('auth.validation.captcha'), 1);
        }

        if (!$user->is_registered) {
            return json(trans('auth.validation.user'), 2);
        } else {
            if ($user->checkPasswd($request->input('password'))) {
                Session::forget('login_fails');

                Session::put('uid'  , $user->uid);
                Session::put('token', $user->getToken());

                $time = $request->input('keep') == true ? 86400 : 3600;

                setcookie('uid',   $user->uid, time()+$time, '/');
                setcookie('token', $user->getToken(), time()+$time, '/');

                return json(trans('auth.login.success'), 0, [
                    'token' => $user->getToken()
                ]);
            } else {
                Session::put('login_fails', session('login_fails', 0) + 1);

                return json(trans('auth.validation.password'), 1, [
                    'login_fails' => session('login_fails')
                ]);
            }
        }
    }

    public function logout()
    {
        if (Session::has('token')) {
            setcookie('uid',   '', time() - 3600, '/');
            setcookie('token', '', time() - 3600, '/');

            Session::flush();
            Session::regenerate();

            return json(trans('auth.logout.success'), 0);
        } else {
            return json(trans('auth.logout.fail'), 1);
        }
    }

    public function register()
    {
        if (Option::get('user_can_register') == 1) {
            return view('auth.register');
        } else {
            throw new PrettyPageException(trans('auth.register.close'), 7);
        }
    }

    public function handleRegister(Request $request)
    {
        if (strtolower($request->input('captcha')) != strtolower(session('phrase')))
            return json(trans('auth.validation.captcha'), 1);

        $this->validate($request, [
            'email'    => 'required|email',
            'password' => 'required|min:8|max:16',
            'nickname' => 'required|nickname|max:255'
        ]);

        $user = new User(null, ['email' => $request->input('email')]);

        if (!$user->is_registered) {
            if (Option::get('user_can_register') == 1) {
                $ip = get_real_ip();

                // If amount of registered accounts of IP is more than allowed amounts,
                // then reject the register.
                if (UserModel::where('ip', $ip)->count() < Option::get('regs_per_ip'))
                {
                    // register new user
                    $user = $user->register($request->input('password'), $ip);
                    $user->setNickName($request->input('nickname'));

                    // set cookies
                    setcookie('uid',   $user->uid, time() + 3600, '/');
                    setcookie('token', $user->getToken(), time() + 3600, '/');

                    return json([
                        'errno' => 0,
                        'msg' => trans('auth.register.success'),
                        'token' => $user->getToken()
                    ]);

                } else {
                    return json(trans('auth.register.max', ['regs' => Option::get('regs_per_ip')]), 7);
                }
            } else {
                return json(trans('auth.register.close'), 7);
            }
        } else {
            return json(trans('auth.register.registered'), 5);
        }
    }

    public function forgot()
    {
        if (config('mail.host') != "") {
            return view('auth.forgot');
        } else {
            throw new PrettyPageException(trans('auth.forgot.close'), 8);
        }
    }

    public function handleForgot(Request $request)
    {
        if (strtolower($request->input('captcha')) != strtolower(session('phrase')))
            return json(trans('auth.validation.captcha'), 1);

        if (config('mail.host') == "")
            return json(trans('auth.forgot.close'), 1);

        if (Session::has('last_mail_time') && (time() - session('last_mail_time')) < 60)
            return json(trans('auth.forgot.frequent-mail'), 1);

        $user = new User(null, ['email' => $request->input('email')]);

        if (!$user->is_registered)
            return json(trans('auth.forgot.unregistered'), 1);

        $uid   = $user->uid;
        $token = base64_encode($user->getToken().substr(time(), 4, 6).Utils::generateRndString(16));

        $url = Option::get('site_url')."/auth/reset?uid=$uid&token=$token";

        try {
            Mail::send('auth.mail', ['reset_url' => $url], function ($m) use ($request) {
                $site_name = Option::get('site_name');

                $m->from(config('mail.username'), $site_name);
                $m->to($request->input('email'))->subject(trans('auth.mail.title', ['sitename' => $site_name]));
            });
        } catch(\Exception $e) {
            return json(trans('auth.mail.failed', ['msg' => $e->getMessage()]), 2);
        }

        Session::put('last_mail_time', time());

        return json(trans('auth.mail.success'), 0);
    }

    public function reset()
    {
        if (isset($_GET['uid']) && isset($_GET['token'])) {
            $user = new User($_GET['uid']);
            if (!$user->is_registered)
                return redirect('auth/forgot')->with('msg', trans('auth.reset.invalid'));

            $token = substr(base64_decode($_GET['token']), 0, -22);

            if ($user->getToken() != $token) {
                return redirect('auth/forgot')->with('msg', trans('auth.reset.invalid'));
            }

            $timestamp = substr(base64_decode($_GET['token']), strlen($token), 6);

            // more than 1 hour
            if ((substr(time(), 4, 6) - $timestamp) > 3600) {
                return redirect('auth/forgot')->with('msg', trans('auth.reset.expired'));
            }

            return view('auth.reset')->with('user', $user);
        } else {
            return redirect('auth/login')->with('msg', trans('auth.check.anonymous'));
        }
    }

    public function handleReset(Request $request)
    {
        $this->validate($request, [
            'uid'      => 'required|integer',
            'password' => 'required|min:8|max:16',
        ]);

        $user = new User($request->input('uid'));

        $user->changePasswd($request->input('password'));

        return json(trans('auth.reset.success'), 0);

    }

    public function captcha()
    {
        $builder = new \Gregwar\Captcha\CaptchaBuilder;
        $builder->build($width = 100, $height = 34);
        Session::put('phrase', $builder->getPhrase());
        $builder->output();

        return \Response::png();
    }

}

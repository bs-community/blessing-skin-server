<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use App\Models\User;
use App\Models\UserModel;
use App\Exceptions\PrettyPageException;
use Validate;
use Mail;
use View;
use Utils;
use Option;
use Http;
use Session;

class AuthController extends BaseController
{
    public function login()
    {
        return view('auth.login');
    }

    public function handleLogin()
    {
        // instantiate user
        $user = (session('auth_type') == 'email') ?
                    new User(null, ['email'    => $_POST['email']]) :
                    new User(null, ['username' => $_POST['username']]);

        if (session('login_fails', 0) > 3) {
            if (strtolower(Utils::getValue('captcha', $_POST)) != strtolower(session('phrase')))
                View::json('验证码填写错误', 1);
        }

        if (!$user->is_registered) {
            View::json('用户不存在哦', 2);
        } else {
            if ($user->checkPasswd($_POST['password'])) {
                session()->forget('login_fails');

                Session::put('uid'  , $user->uid);
                Session::put('token', $user->getToken());

                $time = $_POST['keep'] == true ? 86400 : 3600;

                setcookie('uid',   $user->uid, time()+$time, '/');
                setcookie('token', $user->getToken(), time()+$time, '/');

                View::json([
                    'errno' => 0,
                    'msg' => '登录成功，欢迎回来~',
                    'token' => $user->getToken()
                ]);
            } else {
                $fails = session()->has('login_fails') ? session('login_fails') + 1 : 1;
                Session::put('login_fails', $fails);

                View::json([
                    'errno' => 1,
                    'msg' => '邮箱或密码不对哦~',
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

            View::json('登出成功~', 0);
        } else {
            View::json('并没有有效的 session', 1);
        }
    }

    public function register()
    {
        if (Option::get('user_can_register') == 1) {
            return view('auth.register');
        } else {
            throw new PrettyPageException('残念。。本皮肤站已经关闭注册咯 QAQ', 7);
        }
    }

    public function handleRegister()
    {
        if (strtolower(Utils::getValue('captcha', $_POST)) != strtolower(session('phrase')))
            View::json('验证码填写错误', 1);

        $user = new User(null, ['email' => $_POST['email']]);

        if (!$user->is_registered) {
            if (Option::get('user_can_register') == 1) {
                if (Validate::password($_POST['password'])) {
                    $ip = get_real_ip();

                    // If amount of registered accounts of IP is more than allowed amounts,
                    // then reject the register.
                    if (UserModel::where('ip', $ip)->count() < Option::get('regs_per_ip'))
                    {
                        if (Validate::nickname(Utils::getValue('nickname', $_POST)))
                            View::json('无效的昵称，昵称不能包含奇怪的字符', 1);

                        // register new user
                        $user = $user->register($_POST['password'], $ip);
                        $user->setNickName($_POST['nickname']);

                        // set cookies
                        setcookie('uid',   $user->uid, time() + 3600, '/');
                        setcookie('token', $user->getToken(), time() + 3600, '/');

                        View::json([
                            'errno' => 0,
                            'msg' => '注册成功，正在跳转~',
                            'token' => $user->getToken()
                        ]);

                    } else {
                        View::json('你最多只能注册 '.Option::get('regs_per_ip').' 个账户哦', 7);
                    }
                }
            } else {
                View::json('残念。。本皮肤站已经关闭注册咯 QAQ', 7);
            }
        } else {
            View::json('这个邮箱已经注册过啦，换一个吧', 5);
        }
    }

    public function forgot()
    {
        if ($_ENV['MAIL_HOST'] != "") {
            return view('auth.forgot');
        } else {
            throw new PrettyPageException('本站已关闭重置密码功能', 8);
        }
    }

    public function handleForgot()
    {
        if (strtolower(Utils::getValue('captcha', $_POST)) != strtolower(session('phrase')))
            View::json('验证码填写错误', 1);

        if ($_ENV['MAIL_HOST'] == "")
            View::json('本站已关闭重置密码功能', 1);

        if (session()->has('last_mail_time') && (time() - session('last_mail_time')) < 60)
            View::json('你邮件发送得太频繁啦，过 60 秒后再点发送吧', 1);

        $user = new User(null, ['email' => $_POST['email']]);

        if (!$user->is_registered)
            View::json('该邮箱尚未注册', 1);

        $mail = new Mail();

        $mail->from(Option::get('site_name'))
             ->to($_POST['email'])
             ->subject('重置您在 '.Option::get('site_name').' 上的账户密码');

        $uid   = $user->uid;
        $token = base64_encode($user->getToken().substr(time(), 4, 6).Utils::generateRndString(16));

        $url = Option::get('site_url')."/auth/reset?uid=$uid&token=$token";

        $mail->content(View::make('auth.mail')->with('reset_url', $url)->render());

        if (!$mail->send()) {
            View::json('邮件发送失败，详细信息：'.$mail->getLastError(), 2);
        } else {
            Session::put('last_mail_time', time());
            View::json('邮件已发送，一小时内有效，请注意查收.', 0);
        }

    }

    public function reset()
    {
        if (isset($_GET['uid']) && isset($_GET['token'])) {
            $user = new User($_GET['uid']);
            if (!$user->is_registered)
                return redirect('auth/forgot')->with('msg', '无效的链接');

            $token = substr(base64_decode($_GET['token']), 0, -22);

            if ($user->getToken() != $token) {
                return redirect('auth/forgot')->with('msg', '无效的链接');
            }

            $timestamp = substr(base64_decode($_GET['token']), strlen($token), 6);

            // more than 1 hour
            if ((substr(time(), 4, 6) - $timestamp) > 3600) {
                return redirect('auth/forgot')->with('msg', '链接已过期');
            }

            return View::make('auth.reset')->with('user', $user);
        } else {
            return redirect('auth/login')->with('msg', '非法访问');
        }
    }

    public function handleReset()
    {
        Validate::checkPost(['uid', 'password']);

        if (Validate::password($_POST['password'])) {
            $user = new User($_POST['uid']);

            $user->changePasswd($_POST['password']);

            View::json('密码重置成功', 0);
        }

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

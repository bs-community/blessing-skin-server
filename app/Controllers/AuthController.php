<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\UserModel;
use App\Exceptions\E;
use Mail;
use View;
use Utils;
use Option;

class AuthController extends BaseController
{
    public function login()
    {
        View::show('auth.login');
    }

    public function handleLogin()
    {
        $user = new User($_POST['email']);

        if (Utils::getValue('login_fails', $_SESSION) > 3) {
            if (strtolower(Utils::getValue('captcha', $_POST)) != strtolower($_SESSION['phrase']))
                View::json('验证码填写错误', 1);
        }

        if (!$user->is_registered) {
            View::json('用户不存在哦', 2);
        } else {
            if ($user->checkPasswd($_POST['password'])) {
                $_SESSION['token'] = $user->getToken();
                unset($_SESSION['login_fails']);

                header('Content-type: application/json');

                // setcookie('email', $user->email, time()+3600, '/');
                // setcookie('token', $user->getToken(), time()+3600, '/');

                echo json_encode([
                    'errno' => 0,
                    'msg' => '登录成功，欢迎回来~',
                    'token' => $user->getToken()
                ]);
            } else {
                $_SESSION['login_fails'] = isset($_SESSION['login_fails']) ?
                    $_SESSION['login_fails'] + 1 : 1;
                View::json([
                    'errno' => 1,
                    'msg' => '邮箱或密码不对哦~',
                    'login_fails' => $_SESSION['login_fails']
                ]);
            }
        }
    }

    public function logout()
    {
        if (isset($_SESSION['token'])) {
            session_destroy();
            View::json('登出成功~', 0);
        } else {
            throw new E('并没有有效的 session', 1);
        }
    }

    public function register()
    {
        if (Option::get('user_can_register') == 1) {
            View::show('auth.register');
        } else {
            throw new E('残念。。本皮肤站已经关闭注册咯 QAQ', 7, true);
        }
    }

    public function handleRegister()
    {
        if (strtolower(Utils::getValue('captcha', $_POST)) != strtolower($_SESSION['phrase']))
            View::json('验证码填写错误', 1);

        $user = new User($_POST['email']);

        if (!$user->is_registered) {
            if (Option::get('user_can_register') == 1) {
                if (\Validate::password($_POST['password'])) {
                    // If amount of registered accounts of IP is more than allowed mounts,
                    // then reject the registration.
                    if (count(UserModel::where('ip', \Http::getRealIP())->get()) < Option::get('regs_per_ip')) {
                        // use once md5 to encrypt password
                        $user = $user->register($_POST['password'], \Http::getRealIP());
                        $user->setNickName($_POST['nickname']);

                        echo json_encode([
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
            View::show('auth.forgot');
        } else {
            throw new E('本站已关闭重置密码功能', 8, true);
        }
    }

    public function handleForgot()
    {
        if (strtolower(Utils::getValue('captcha', $_POST)) != strtolower($_SESSION['phrase']))
            View::json('验证码填写错误', 1);

        if ($_ENV['MAIL_HOST'] == "")
            View::json('本站已关闭重置密码功能', 1);

        if (isset($_SESSION['last_mail_time']) && (time() - $_SESSION['last_mail_time']) < 60)
            View::json('你邮件发送得太频繁啦，过 60 秒后再点发送吧', 1);

        $user = new User($_POST['email']);

        if (!$user->is_registered)
            View::json('该邮箱尚未注册', 1);

        $mail = new Mail();

        $mail->from(Option::get('site_name'))
             ->to($_POST['email'])
             ->subject('重置您在 '.Option::get('site_name').' 上的账户密码');

        $uid = $user->uid;
        $token = base64_encode($user->getToken().substr(time(), 4, 6).Utils::generateRndString(16));

        $url = Option::get('site_url')."/auth/reset?uid={$uid}&token=$token";
        $content = View::make('auth.mail')->with('reset_url', $url)->render();

        if(!$mail->content($content)->send()) {
            View::json('邮件发送失败，详细信息：'.$mail->getLastError(), 2);
        } else {
            $_SESSION['last_mail_time'] = time();
            View::json('邮件已发送，一小时内有效，请注意查收.', 0);
        }

    }

    public function reset()
    {
        if (isset($_GET['uid']) && isset($_GET['token'])) {
            $user = new User('', $_GET['uid']);
            if (!$user->is_registered)
                \Http::redirect('./forgot', '无效的链接');

            $token = substr(base64_decode($_GET['token']), 0, -22);

            if ($user->getToken() != $token) {
                \Http::redirect('./forgot', '无效的链接');
            }

            $timestamp = substr(base64_decode($_GET['token']), strlen($token), 6);

            // more than 1 hour
            if ((substr(time(), 4, 6) - $timestamp) > 3600) {
                \Http::redirect('./forgot', '链接已过期');
            }

            echo View::make('auth.reset')->with('user', $user);
        } else {
            \Http::redirect('./login', '非法访问');
        }
    }

    public function handleReset()
    {
        \Validate::checkPost(['uid', 'password']);

        if (\Validate::password($_POST['password'])) {
            $user = new User('', $_POST['uid']);

            $user->changePasswd($_POST['password']);

            View::json('密码重置成功', 0);
        }

    }

    public function captcha()
    {
        $builder = new \Gregwar\Captcha\CaptchaBuilder;
        $builder->build($width = 100, $height = 34);
        $_SESSION['phrase'] = $builder->getPhrase();
        header('Content-type: image/jpeg');
        $builder->output();
    }

}

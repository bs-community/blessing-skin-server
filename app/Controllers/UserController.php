<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Texture;
use App\Exceptions\E;
use Utils;
use View;

class UserController extends BaseController
{
    private $action = "";
    private $user = null;

    function __construct()
    {
        $this->action = isset($_GET['action']) ? $_GET['action'] : "";
        $this->user = new User($_SESSION['email']);
    }

    public function index()
    {
        echo View::make('user.index')->with('user', $this->user)->render();
    }

    public function sign()
    {
        if ($aquired_score = $this->user->sign()) {
            View::json([
                'errno' => 0,
                'msg'   => '签到成功，获得了 '.$aquired_score.' 积分~',
                'score' => $this->user->getScore()
            ]);
        } else {
            View::json($this->user->canSign(true).' 小时后才能再次签到哦~', 1);
        }
    }

    public function profile()
    {
        echo View::make('user.profile')->with('user', $this->user);
    }

    public function handleProfile()
    {
        // handle changing nickname
        if ($this->action == "nickname") {
            if (!isset($_POST['new_nickname'])) throw new E('Invalid parameters.');

            if (Utils::convertString($_POST['new_nickname']) != $_POST['new_nickname'])
                View::json('无效的昵称。昵称中包含了奇怪的字符。', 1);

            if ($this->user->setNickName($_POST['new_nickname']))
                View::json('昵称已成功设置为 '.$_POST['new_nickname'], 0);
        // handle changing password
        } elseif ($this->action == "password") {
            if (!(isset($_POST['current_password']) && isset($_POST['new_password'])))
                throw new E('Invalid parameters.');

            if (!$this->user->checkPasswd($_POST['current_password']))
                View::json('原密码错误', 1);

            if (\Validate::checkValidPwd($_POST['new_password'])) {
                if ($this->user->changePasswd($_POST['new_password']))
                    View::json('密码修改成功，请重新登录', 0);
            }
        // handle changing email
        } elseif ($this->action == "email") {
            if (!(isset($_POST['new_email']) && isset($_POST['password'])))
                throw new E('Invalid parameters.');

            if (!filter_var($_POST['new_email'], FILTER_VALIDATE_EMAIL)) {
                View::json('邮箱格式错误', 3);
            }

            if (!$this->user->checkPasswd($_POST['password']))
                View::json('密码错误', 1);

            if ($this->user->setEmail($_POST['new_email']))
                View::json('邮箱修改成功，请重新登录', 0);

        // handle deleting account
        } elseif ($this->action == "delete") {
            if (!isset($_POST['password']))
                throw new E('Invalid parameters.');

            if (!$this->user->checkPasswd($_POST['password']))
                View::json('密码错误', 1);

            if ($this->user->delete())
                View::json('账号已被成功删除', 0);

        }

    }

    public function config()
    {
        echo View::make('user.config')->with('user', $this->user);
    }

    public function setAvatar()
    {
        if (!isset($_POST['tid'])) throw new E('Empty tid.');

        $result = Texture::find($_POST['tid']);
        if ($result) {
            if ($result->type == "cape") throw new E('披风可不能设置为头像哦~', 1);

            if ((new User($_SESSION['email']))->setAvatar($_POST['tid'])) {
                View::json('设置成功！', 0);
            }
        } else {
            throw new E('材质不存在。', 1);
        }
    }

}

<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\UserModel;
use App\Exceptions\E;
use Option;
use Utils;
use View;

class AdminController extends BaseController
{

    public function index()
    {
        echo View::make('admin.index')->render();
    }

    public function ajaxHandler()
    {
        $action = isset($_GET['action']) ? $_GET['action'] : "";

        if ($action == "color") {
            Utils::checkPost(['color_scheme']);

            $color_scheme = str_replace('_', '-', $_POST['color_scheme']);
            Option::set('color_scheme', $color_scheme);

            View::json('修改配色成功', 0);
        }

        $user = new User('', Utils::getValue('uid', $_POST));

        if (!$user->is_registered)
            throw new E('用户不存在', 1);

        if ($action == "email") {
            Utils::checkPost(['email']);

            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                View::json('邮箱格式错误', 3);
            }

            if ($user->setEmail($_POST['email']))
                View::json('邮箱修改成功', 0);
        } if ($action == "nickname") {
            Utils::checkPost(['nickname']);

            if (Utils::convertString($_POST['nickname']) != $_POST['nickname'])
                View::json('无效的昵称。昵称中包含了奇怪的字符。', 1);

            if ($user->setNickName($_POST['nickname']))
                View::json('昵称已成功设置为 '.$_POST['nickname'], 0);
        } else if ($action == "password") {
            Utils::checkPost(['password']);

            if (\Validate::checkValidPwd($_POST['password'])) {
                if ($user->changePasswd($_POST['password']))
                    View::json('密码修改成功', 0);
            }
        } else if ($action == "score") {
            Utils::checkPost(['score']);

            if ($user->setScore($_POST['score']))
                    View::json('积分修改成功', 0);
        } else if ($action == "delete") {
            if ($user->delete())
                View::json('账号已被成功删除', 0);
        } else {
            throw new E('Illegal parameters', 1);
        }
    }

    public function customize()
    {
        echo View::make('admin.customize')->render();
    }

    public function options()
    {
        echo View::make('admin.options')->render();
    }

    public function users()
    {/*
        for ($i=0; $i < 60; $i++) {
            $user = new UserModel();
            $user->email = Utils::generateRndString(6)."@".Utils::generateRndString(3).".com";
            $user->nickname = Utils::generateRndString(5);
            $user->score = 666;
            $user->ip = '111.111.111.111';
            $user->permission = "0";
            $user->register_at = Utils::getTimeFormatted();
            $user->save();

            echo "Seed: ".$user->email." added. <br />";
        }
        exit;*/

        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $filter = isset($_GET['filter']) ? $_GET['filter'] : "";

        if ($filter == "") {
            $users = UserModel::orderBy('uid');
            $total_pages = ceil($users->count() / 30);
            $users = $users->skip(($page - 1) * 30)->take(30)->get();
        } else {
            $users = UserModel::like('nickname', $filter)->orderBy('uid');
            $total_pages = ceil($users->count() / 30);
            $users = $users->skip(($page - 1) * 30)->take(30)->get();
        }

        echo View::make('admin.users')->with('users', $users)
                                        ->with('page', $page)
                                        ->with('total_pages', $total_pages)
                                        ->render();
    }

    public function players()
    {
        echo View::make('admin.players')->render();
    }

}

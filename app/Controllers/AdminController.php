<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\UserModel;
use App\Models\Player;
use App\Models\PlayerModel;
use App\Models\Texture;
use App\Exceptions\E;
use Utils;
use View;

class AdminController extends BaseController
{

    public function index()
    {
        View::show('admin.index');
    }

    public function customize()
    {
        View::show('admin.customize');
    }

    public function options()
    {
        View::show('admin.options');
    }

    public function users()
    {
        $page = isset($_GET['page']) ? $_GET['page'] : 1;

        $filter = isset($_GET['filter']) ? $_GET['filter'] : "";

        $q = isset($_GET['q']) ? $_GET['q'] : "";

        if ($filter == "") {
            $users = UserModel::orderBy('uid');
            $total_pages = ceil($users->count() / 30);
            $users = $users->skip(($page - 1) * 30)->take(30)->get();
        } else if ($filter == "email") {
            $users = UserModel::like('email', $q)->orderBy('uid');
            $total_pages = ceil($users->count() / 30);
            $users = $users->skip(($page - 1) * 30)->take(30)->get();
        } else if ($filter == "nickname") {
            $users = UserModel::like('nickname', $q)->orderBy('uid');
            $total_pages = ceil($users->count() / 30);
            $users = $users->skip(($page - 1) * 30)->take(30)->get();
        }

        echo View::make('admin.users')->with('users', $users)
                                      ->with('filter', $filter)
                                      ->with('q', $q)
                                      ->with('page', $page)
                                      ->with('total_pages', $total_pages)
                                      ->render();
    }

    public function players()
    {
        $page = isset($_GET['page']) ? $_GET['page'] : 1;

        $filter = isset($_GET['filter']) ? $_GET['filter'] : "";

        $q = isset($_GET['q']) ? $_GET['q'] : "";

        if ($filter == "") {
            $players = PlayerModel::orderBy('uid');
            $total_pages = ceil($players->count() / 30);
            $players = $players->skip(($page - 1) * 30)->take(30)->get();
        } else if ($filter == "player_name") {
            $players = PlayerModel::like('player_name', $q)->orderBy('uid');
            $total_pages = ceil($players->count() / 30);
            $players = $players->skip(($page - 1) * 30)->take(30)->get();
        } else if ($filter == "uid") {
            $players = PlayerModel::where('uid', $q)->orderBy('uid');
            $total_pages = ceil($players->count() / 30);
            $players = $players->skip(($page - 1) * 30)->take(30)->get();
        }

        echo View::make('admin.players')->with('players', $players)
                                        ->with('filter', $filter)
                                        ->with('q', $q)
                                        ->with('page', $page)
                                        ->with('total_pages', $total_pages)
                                        ->render();
    }

    /**
     * Handle ajax request from /admin/users
     */
    public function userAjaxHandler()
    {
        $action = isset($_GET['action']) ? $_GET['action'] : "";

        if ($action == "color") {
            Utils::checkPost(['color_scheme']);

            $color_scheme = str_replace('_', '-', $_POST['color_scheme']);
            \Option::set('color_scheme', $color_scheme);

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

        } else if ($action == "permission") {
            $permission = $user->getPermission() == "0"
                            ? "-1" : "0";

            if ($user->setPermission($permission))
                    View::json('账号已被'.($permission == "-1"?"封禁":"解封"), 0);

        } else if ($action == "delete") {
            if ($user->delete())
                View::json('账号已被成功删除', 0);

        } else {
            throw new E('Illegal parameters', 1);
        }
    }

    /**
     * Handle ajax request from /admin/players
     */
    public function playerAjaxHandler()
    {
        $action = isset($_GET['action']) ? $_GET['action'] : "";

        // exception will be throw by model if player is not existent
        $player = new Player(Utils::getValue('pid', $_POST));

        if ($action == "preference") {
            Utils::checkPost(['preference']);

            if ($_POST['preference'] != "default" && $_POST['preference'] != "slim")
                View::json('无效的参数', 0);

            if ($player->setPreference($_POST['preference']))
                View::json('角色 '.$player->player_name.' 的优先模型已更改至 '.$_POST['preference'], 0);

        } elseif ($action == "texture") {
            Utils::checkPost(['model', 'tid']);

            if ($_POST['model'] != "steve" && $_POST['model'] != "alex" && $_POST['model'] != "cape")
                View::json('无效的参数', 0);

            if (!(is_numeric($_POST['tid']) && Texture::find($_POST['tid'])))
                View::json('材质 tid.'.$_POST['tid'].' 不存在', 1);

            if ($player->setTexture(['tid_'.$_POST['model'] => $_POST['tid']]))
                View::json('角色 '.$player->player_name.' 的材质修改成功', 0);

        } elseif ($action == "owner") {
            Utils::checkPost(['uid']);

            if (!is_numeric($_POST['uid']))
                View::json('无效的参数', 0);

            $user = new User('', $_POST['uid']);

            if (!$user->is_registered)
                View::json('不存在的用户', 1);

            if ($player->setOwner($_POST['uid']))
                View::json('角色 '.$player->player_name.' 已成功让渡至 '.$user->getNickName(), 0);

        } elseif ($action == "delete") {
            if (PlayerModel::where('pid', $_POST['pid'])->delete())
                View::json('角色已被成功删除', 0);
        } else {
            throw new E('Illegal parameters', 1);
        }
    }

}

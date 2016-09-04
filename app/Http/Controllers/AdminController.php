<?php

namespace App\Http\Controllers;

use View;
use Utils;
use App\Models\User;
use App\Models\Player;
use App\Models\Texture;
use App\Models\UserModel;
use App\Models\PlayerModel;
use Illuminate\Http\Request;
use App\Exceptions\PrettyPageException;

class AdminController extends Controller
{

    public function index()
    {
        return view('admin.index');
    }

    public function customize()
    {
        return view('admin.customize');
    }

    public function score()
    {
        return view('admin.score');
    }

    public function options()
    {
        return view('admin.options');
    }

    /**
     * Handle Upload Checking & Downloading
     *
     * @param  Request $request
     * @return void
     */
    public function update(Request $request)
    {
        if ($request->action == "check") {
            $updater = new \Updater(\App::version());

            if ($updater->newVersionAvailable()) {
                View::json([
                    'new_version_available' => true,
                    'latest_version' => $updater->latest_version
                ]);
            } else {
                View::json([
                    'new_version_available' => false,
                    'latest_version' => $updater->current_version
                ]);
            }
        } elseif ($request->action == "download") {
            return view('admin.download');
        } else {
            return view('admin.update');
        }
    }

    /**
     * Show Manage Page of Users.
     *
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function users(Request $request)
    {
        $page   = $request->input('page', 1);
        $filter = $request->input('filter', '');
        $q      = $request->input('q', '');

        if ($filter == "") {
            $users = UserModel::orderBy('uid');
        } elseif ($filter == "email") {
            $users = UserModel::like('email', $q)->orderBy('uid');
        } elseif ($filter == "nickname") {
            $users = UserModel::like('nickname', $q)->orderBy('uid');
        }

        $total_pages = ceil($users->count() / 30);
        $users = $users->skip(($page - 1) * 30)->take(30)->get();

        return view('admin.users')->with('users', $users)
                                  ->with('filter', $filter)
                                  ->with('q', $q)
                                  ->with('page', $page)
                                  ->with('total_pages', $total_pages);
    }

    /**
     * Show Manage Page of Players.
     *
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function players(Request $request)
    {
        $page   = $request->input('page', 1);
        $filter = $request->input('filter', '');
        $q      = $request->input('q', '');

        if ($filter == "") {
            $players = PlayerModel::orderBy('uid');
        } elseif ($filter == "player_name") {
            $players = PlayerModel::like('player_name', $q)->orderBy('uid');
        } elseif ($filter == "uid") {
            $players = PlayerModel::where('uid', $q)->orderBy('uid');
        }

        $total_pages = ceil($players->count() / 30);
        $players = $players->skip(($page - 1) * 30)->take(30)->get();

        return view('admin.players')->with('players', $players)
                                    ->with('filter', $filter)
                                    ->with('q', $q)
                                    ->with('page', $page)
                                    ->with('total_pages', $total_pages);
    }

    /**
     * Handle ajax request from /admin/users
     *
     * @param  Request $request
     * @return void
     */
    public function userAjaxHandler(Request $request)
    {
        $action = $request->input('action');

        if ($action == "color") {
            $this->validate($request, [
                'color_scheme' => 'required'
            ]);

            $color_scheme = str_replace('_', '-', $request->input('color_scheme'));
            \Option::set('color_scheme', $color_scheme);

            View::json('修改配色成功', 0);
        }

        $user     = new User($request->input('uid'));
        // current user
        $cur_user = new User(session('uid'));

        if (!$user->is_registered)
            View::json('用户不存在', 1);

        if ($action == "email") {
            $this->validate($request, [
                'email' => 'required|email'
            ]);

            if ($user->setEmail($request->input('email')))
                View::json('邮箱修改成功', 0);

        } elseif ($action == "nickname") {
            $this->validate($request, [
                'nickname' => 'required|nickname'
            ]);

            if ($user->setNickName($request->input('nickname')))
                View::json('昵称已成功设置为 '.$request->input('nickname'), 0);

        } elseif ($action == "password") {
            $this->validate($request, [
                'password' => 'required|min:8|max:16'
            ]);

            if ($user->changePasswd($request->input('password')))
                View::json('密码修改成功', 0);

        } elseif ($action == "score") {
            $this->validate($request, [
                'score' => 'required|integer'
            ]);

            if ($user->setScore($request->input('score')))
                View::json('积分修改成功', 0);

        } elseif ($action == "ban") {
            if ($user->getPermission() == "1") {
                if ($cur_user->getPermission() != "2")
                    View::json('非超级管理员无法封禁普通管理员');
            } elseif ($user->getPermission() == "2") {
                View::json('超级管理员无法被封禁');
            }

            $permission = $user->getPermission() == "-1" ? "0" : "-1";

            if ($user->setPermission($permission)) {
                View::json([
                    'errno'      => 0,
                    'msg'        => '账号已被' . ($permission == '-1' ? '封禁' : '解封'),
                    'permission' => $user->getPermission()
                ]);
            }

        } elseif ($action == "admin") {
            if ($cur_user->getPermission() != "2")
                View::json('非超级管理员无法进行此操作');

            if ($user->getPermission() == "2")
                View::json('超级管理员无法被解除');

            $permission = $user->getPermission() == "1" ? "0" : "1";

            if ($user->setPermission($permission)) {
                View::json([
                    'errno'      => 0,
                    'msg'        => '账号已被' . ($permission == '1' ? '设为' : '解除') . '管理员',
                    'permission' => $user->getPermission()
                ]);
            }

        } elseif ($action == "delete") {
            if ($user->delete())
                View::json('账号已被成功删除', 0);

        } else {
            View::json('非法参数', 1);
        }
    }

    /**
     * Handle ajax request from /admin/players
     */
    public function playerAjaxHandler(Request $request)
    {
        $action = isset($_GET['action']) ? $_GET['action'] : "";

        // exception will be throw by model if player is not existent
        $player = new Player($request->input('pid'));

        if ($action == "preference") {
            $this->validate($request, [
                'preference' => 'required|preference'
            ]);

            if ($player->setPreference($request->input('preference')))
                View::json('角色 '.$player->player_name.' 的优先模型已更改至 '.$request->input('preference'), 0);

        } elseif ($action == "texture") {
            $this->validate($request, [
                'model' => 'required|model',
                'tid'   => 'required|integer'
            ]);

            if (!Texture::find($request->tid))
                View::json("材质 tid.{$request->tid} 不存在", 1);

            if ($player->setTexture(['tid_'.$request->model => $request->tid]))
                View::json("角色 {$player->player_name} 的材质修改成功", 0);

        } elseif ($action == "owner") {
            $this->validate($request, [
                'tid'   => 'required|integer'
            ]);

            $user = new User($request->input('uid'));

            if (!$user->is_registered)
                View::json('不存在的用户', 1);

            if ($player->setOwner($request->input('uid')))
                View::json("角色 $player->player_name 已成功让渡至 ".$user->getNickName(), 0);

        } elseif ($action == "delete") {
            if (PlayerModel::where('pid', $request->input('pid'))->delete())
                View::json('角色已被成功删除', 0);
        } else {
            View::json('非法参数', 1);
        }
    }

}

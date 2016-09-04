<?php

namespace App\Http\Controllers;

use View;
use Utils;
use App\Models\User;
use App\Models\Texture;
use Illuminate\Http\Request;
use App\Exceptions\PrettyPageException;

class UserController extends Controller
{
    private $action = "";
    private $user   = null;

    public function __construct()
    {
        $this->action = isset($_GET['action']) ? $_GET['action'] : "";
        $this->user   = new User(session('uid'));
    }

    public function index()
    {
        return view('user.index')->with('user', $this->user);
    }

    /**
     * Handle User Signing
     *
     * @return void
     */
    public function sign()
    {
        if ($aquired_score = $this->user->sign()) {
            View::json([
                'errno'          => 0,
                'msg'            => "签到成功，获得了 $aquired_score 积分~",
                'score'          => $this->user->getScore(),
                'remaining_time' => $this->user->canSign(true)
            ]);
        } else {
            View::json($this->user->canSign(true).' 小时后才能再次签到哦~', 1);
        }
    }

    public function profile()
    {
        return view('user.profile')->with('user', $this->user);
    }

    /**
     * Handle Changing Profile
     *
     * @param  Request $request
     * @return void
     */
    public function handleProfile(Request $request)
    {
        switch ($this->action) {
            case 'nickname':
                $this->validate($request, [
                    'new_nickname' => 'required|nickname|max:255'
                ]);

                $nickname = $request->input('new_nickname');

                if ($this->user->setNickName($nickname))
                    View::json("昵称已成功设置为 $nickname", 0);

                break;

            case 'password':
                $this->validate($request, [
                    'current_password' => 'required|min:8|max:16',
                    'new_password'     => 'required|min:8|max:16'
                ]);

                if (!$this->user->checkPasswd($request->input('current_password')))
                    View::json('原密码错误', 1);

                if ($this->user->changePasswd($request->input('new_password')))
                    View::json('密码修改成功，请重新登录', 0);

                break;

            case 'email':
                $this->validate($request, [
                    'new_email' => 'required|email',
                    'password'  => 'required|min:8|max:16'
                ]);

                if (!$this->user->checkPasswd($request->input('password')))
                    View::json('密码错误', 1);

                if ($this->user->setEmail($request->input('new_email')))
                    View::json('邮箱修改成功，请重新登录', 0);

                break;

            case 'delete':
                $this->validate($request, [
                    'password' => 'required|min:8|max:16'
                ]);

                if (!$this->user->checkPasswd($request->input('password')))
                    View::json('密码错误', 1);

                if ($this->user->delete()) {
                    setcookie('uid',   '', time() - 3600, '/');
                    setcookie('token', '', time() - 3600, '/');

                    Session::flush();
                    Session::save();

                    View::json('账号已被成功删除', 0);
                }

                break;

            default:
                View::json('非法参数', 1);
                break;
        }

    }

    public function config()
    {
        return view('user.config')->with('user', $this->user);
    }

    /**
     * Set Avatar for User
     *
     * @param Request $request
     */
    public function setAvatar(Request $request)
    {
        $this->validate($request, [
            'tid' => 'required|integer'
        ]);

        $result = Texture::find($request->input('tid'));

        if ($result) {
            if ($result->type == "cape")
                View::json('披风可不能设置为头像哦~', 1);

            if ($this->user->setAvatar($request->input('tid'))) {
                View::json('设置成功！', 0);
            }
        } else {
            View::json('材质不存在。', 1);
        }
    }

}

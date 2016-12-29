<?php

namespace App\Http\Controllers;

use View;
use Utils;
use Option;
use App\Events;
use App\Models\User;
use App\Models\Player;
use App\Models\Texture;
use Illuminate\Http\Request;
use App\Exceptions\PrettyPageException;
use App\Services\Repositories\UserRepository;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.index');
    }

    public function customize()
    {
        $homepage = Option::form('homepage', '首页配置', function($form)
        {
            $form->text('home_pic_url', '首页图片地址')->hint('相对于首页的路径或者完整的 URL');

            $form->select('copyright_prefer', '程序版权信息')
                ->option('0', 'Powered with ❤ by Blessing Skin Server.')
                ->option('1', 'Powered by Blessing Skin Server.')
                ->option('2', '由 Blessing Skin Server 强力驱动.')
                ->option('3', '自豪地采用 Blessing Skin Server.');

            $form->textarea('copyright_text', '自定义版权文字')->rows(6)
                ->description('自定义版权文字内可使用占位符，<code>{site_name}</code> 将会被自动替换为站点名称，<code>{site_url}</code> 会被替换为站点地址。');

        })->handle();

        $customJsCss = Option::form('customJsCss', '自定义 CSS/JavaScript', function($form)
        {
            $form->textarea('custom_css', 'CSS')->rows(6);
            $form->textarea('custom_js', 'JavaScript')->rows(6);
        })->addMessage('
            内容将会被追加至每个页面的 &lt;style&gt; 和 &lt;script&gt; 标签中。<br>
            - 这里有一些有用的示例：<a href="https://github.com/printempw/blessing-skin-server/wiki/%E3%80%8C%E8%87%AA%E5%AE%9A%E4%B9%89-CSS-JavaScript%E3%80%8D%E5%8A%9F%E8%83%BD%E7%9A%84%E4%B8%80%E4%BA%9B%E5%AE%9E%E4%BE%8B">「自定义 CSS JavaScript」功能的一些实例@GitHub WiKi</a>
        ')->handle();

        return view('admin.customize', ['forms' => compact('homepage', 'customJsCss')]);
    }

    public function score()
    {
        $rate = Option::form('rate', '积分换算', function($form)
        {
            $form->group('score_per_storage', '存储')->text('score_per_storage')->addon('积分 = 1 KB');

            $form->group('private_score_per_storage', '私密材质存储')
                ->text('private_score_per_storage')->addon('积分 = 1 KB')
                ->hint('上传私密材质将消耗更多积分');

            $form->group('score_per_closet_item', '收藏消耗积分')
                ->text('score_per_closet_item')->addon('积分 = 一个衣柜物品');

            $form->checkbox('return_score', '积分返还')->label('用户删除角色/材质/收藏时返还积分');

            $form->group('score_per_player', '角色')->text('score_per_player')->addon('积分 = 一个角色');

            $form->text('user_initial_score', '新用户默认积分');

        })->handle();

        $signIn = Option::form('sign_in', '签到配置', function($form)
        {
            $form->group('sign_score', '签到获得积分')
                ->text('sign_score_from')->addon('积分 ~ ')->text('sign_score_to')->addon('积分');

            $form->group('sign_gap_time', '签到间隔时间')->text('sign_gap_time')->addon('小时');

            $form->checkbox('sign_after_zero', '签到时间')->label('每天零点后可签到')
                ->hint('勾选后将无视上一条，每天零时后均可签到');
        })->handle(function() {
            $sign_score = $_POST['sign_score_from'].','.$_POST['sign_score_to'];
            Option::set('sign_score', $sign_score);

            unset($_POST['sign_score_from']);
            unset($_POST['sign_score_to']);
        })->with([
            'sign_score_from' => @explode(',', option('sign_score'))[0],
            'sign_score_to'   => @explode(',', option('sign_score'))[1]
        ]);

        return view('admin.score', ['forms' => compact('rate', 'signIn')]);
    }

    public function options()
    {
        $general = Option::form('general', '常规选项', function($form)
        {
            $form->text('site_name', '站点标题');
            $form->text('site_description', '站点描述');
            $form->text('site_url', '站点地址（URL）')->hint('以 http(s):// 开头，不要以 / 结尾');

            $form->checkbox('user_can_register', '开放注册')->label('任何人都可以注册');

            $form->text('regs_per_ip', '每个 IP 限制注册数');

            $form->group('max_upload_file_size', '最大允许上传大小')
                    ->text('max_upload_file_size')->addon('KB')
                    ->hint('PHP 限制：'.ini_get('upload_max_filesize').'，定义在 php.ini 中。');

            $form->checkbox('allow_chinese_playername', '角色名')->label('允许中文角色名');

            $form->select('api_type', '首选 JSON API')
                    ->option('0', 'CustomSkinLoader API')
                    ->option('1', 'UniversalSkinAPI');

            $form->checkbox('auto_del_invalid_texture', '失效材质')->label('自动删除失效材质')->hint('自动从皮肤库中删除文件不存在的材质记录');

            $form->textarea('comment_script', '评论代码')->rows(6)->description('评论代码内可使用占位符，<code>{tid}</code> 将会被自动替换为材质的 id，<code>{name}</code> 会被替换为材质名称，<code>{url}</code> 会被替换为当前页面地址。');

        })->handle(function() {
            if (substr($_POST['site_url'], -1) == "/")
                $_POST['site_url'] = substr($_POST['site_url'], 0, -1);
        });

        $announcement = Option::form('announcement', '站点公告', function($form)
        {
            $form->textarea('announcement')->description('可使用 Markdown 进行排版');

        })->renderWithOutTable()->handle();

        $cache = Option::form('cache', '资源文件配置', function($form)
        {
            $form->checkbox('force_ssl', '强制 SSL')->label('强制使用 HTTPS 协议加载资源')->hint('请确认 SSL 可用后再开启');
            $form->checkbox('auto_detect_asset_url', '资源地址')->label('自动判断资源文件地址')->hint('根据当前 URL 自动加载资源文件，如果关闭则将根据「站点地址」填写的内容加载。如果出现 CDN 回源问题请关闭');
            $form->checkbox('return_200_when_notfound', 'HTTP 响应码')->label('请求不存在的角色时返回 200 而不是 404');

            $form->text('cache_expire_time', '缓存失效时间')->hint('秒数，86400 = 一天，31536000 = 一年');

        })->type('warning')->hint('如果启用了 CDN 缓存请适当修改这些配置')->handle();

        return view('admin.options')->with('forms', compact('general', 'cache', 'announcement'));
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
            $users = User::orderBy('uid');
        } elseif ($filter == "email") {
            $users = User::like('email', $q)->orderBy('uid');
        } elseif ($filter == "nickname") {
            $users = User::like('nickname', $q)->orderBy('uid');
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
            $players = Player::orderBy('uid');
        } elseif ($filter == "player_name") {
            $players = Player::like('player_name', $q)->orderBy('uid');
        } elseif ($filter == "uid") {
            $players = Player::where('uid', $q)->orderBy('uid');
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
    public function userAjaxHandler(Request $request, UserRepository $users)
    {
        $action = $request->input('action');

        if ($action == "color") {
            $this->validate($request, [
                'color_scheme' => 'required'
            ]);

            $color_scheme = str_replace('_', '-', $request->input('color_scheme'));
            \Option::set('color_scheme', $color_scheme);

            return json('修改配色成功', 0);
        }

        $user     = $users->get($request->input('uid'));
        // current user
        $cur_user = $users->get(session('uid'));

        if (!$user)
            return json('用户不存在', 1);

        if ($action == "email") {
            $this->validate($request, [
                'email' => 'required|email'
            ]);

            if ($user->setEmail($request->input('email')))
                return json('邮箱修改成功', 0);

        } elseif ($action == "nickname") {
            $this->validate($request, [
                'nickname' => 'required|nickname'
            ]);

            if ($user->setNickName($request->input('nickname')))
                return json('昵称已成功设置为 '.$request->input('nickname'), 0);

        } elseif ($action == "password") {
            $this->validate($request, [
                'password' => 'required|min:8|max:16'
            ]);

            if ($user->changePasswd($request->input('password')))
                return json('密码修改成功', 0);

        } elseif ($action == "score") {
            $this->validate($request, [
                'score' => 'required|integer'
            ]);

            if ($user->setScore($request->input('score')))
                return json('积分修改成功', 0);

        } elseif ($action == "ban") {
            if ($user->getPermission() == User::ADMIN) {
                if ($cur_user->getPermission() != User::SUPER_ADMIN)
                    return json('非超级管理员无法封禁普通管理员');
            } elseif ($user->getPermission() == User::SUPER_ADMIN) {
                return json('超级管理员无法被封禁');
            }

            $permission = $user->getPermission() == User::BANNED ? User::NORMAL : User::BANNED;

            if ($user->setPermission($permission)) {
                return json([
                    'errno'      => 0,
                    'msg'        => '账号已被' . ($permission == User::BANNED ? '封禁' : '解封'),
                    'permission' => $user->getPermission()
                ]);
            }

        } elseif ($action == "admin") {
            if ($cur_user->getPermission() != User::SUPER_ADMIN)
                return json('非超级管理员无法进行此操作');

            if ($user->getPermission() == User::SUPER_ADMIN)
                return json('超级管理员无法被解除');

            $permission = $user->getPermission() == User::ADMIN ? User::NORMAL : User::ADMIN;

            if ($user->setPermission($permission)) {
                return json([
                    'errno'      => 0,
                    'msg'        => '账号已被' . ($permission == User::ADMIN ? '设为' : '解除') . '管理员',
                    'permission' => $user->getPermission()
                ]);
            }

        } elseif ($action == "delete") {
            if ($user->delete())
                return json('账号已被成功删除', 0);

        } else {
            return json('非法参数', 1);
        }
    }

    /**
     * Handle ajax request from /admin/players
     */
    public function playerAjaxHandler(Request $request, UserRepository $users)
    {
        $action = isset($_GET['action']) ? $_GET['action'] : "";

        $player = Player::find($request->input('pid'));

        if (!$player)
            abort(404, trans('general.unexistent-player'));

        if ($action == "preference") {
            $this->validate($request, [
                'preference' => 'required|preference'
            ]);

            if ($player->setPreference($request->input('preference')))
                return json('角色 '.$player->player_name.' 的优先模型已更改至 '.$request->input('preference'), 0);

        } elseif ($action == "texture") {
            $this->validate($request, [
                'model' => 'required|model',
                'tid'   => 'required|integer'
            ]);

            if (!Texture::find($request->tid))
                return json("材质 tid.{$request->tid} 不存在", 1);

            if ($player->setTexture(['tid_'.$request->model => $request->tid]))
                return json("角色 {$player->player_name} 的材质修改成功", 0);

        } elseif ($action == "owner") {
            $this->validate($request, [
                'pid'   => 'required|integer',
                'uid'   => 'required|integer'
            ]);

            $user = $users->get($request->input('uid'));

            if (!$user)
                return json('不存在的用户', 1);

            if ($player->setOwner($request->input('uid')))
                return json("角色 $player->player_name 已成功让渡至 ".$user->getNickName(), 0);

        } elseif ($action == "delete") {
            if ($player->delete())
                return json('角色已被成功删除', 0);
        } else {
            return json('非法参数', 1);
        }
    }

}

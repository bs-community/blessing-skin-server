<?php

namespace App\Http\Controllers;

use View;
use Utils;
use Option;
use Datatables;
use App\Events;
use App\Models\User;
use App\Models\Player;
use App\Models\Texture;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Services\OptionForm;
use App\Exceptions\PrettyPageException;
use App\Services\Repositories\UserRepository;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.index');
    }

    public function customize(Request $request)
    {
        if ($request->input('action') == "color") {
            $this->validate($request, [
                'color_scheme' => 'required'
            ]);

            $color_scheme = str_replace('_', '-', $request->input('color_scheme'));
            option(['color_scheme' => $color_scheme]);

            return json(trans('admin.customize.change-color.success'), 0);
        }

        $homepage = Option::form('homepage', OptionForm::AUTO_DETECT, function($form)
        {
            $form->text('home_pic_url')->hint(OptionForm::AUTO_DETECT);

            $form->text('favicon_url')->hint(OptionForm::AUTO_DETECT)
                ->description(OptionForm::AUTO_DETECT);

            $form->select('copyright_prefer')
                    ->option('0', 'Powered with ❤ by Blessing Skin Server.')
                    ->option('1', 'Powered by Blessing Skin Server.')
                    ->option('2', 'Proudly powered by Blessing Skin Server.')
                    ->option('3', '由 Blessing Skin Server 强力驱动.')
                    ->option('4', '自豪地采用 Blessing Skin Server.')
                ->description(OptionForm::AUTO_DETECT);

            $form->textarea('copyright_text')->rows(6)
                ->description(OptionForm::AUTO_DETECT);

        })->handle();

        $customJsCss = Option::form('customJsCss', OptionForm::AUTO_DETECT, function($form)
        {
            $form->textarea('custom_css', 'CSS')->rows(6);
            $form->textarea('custom_js', 'JavaScript')->rows(6);
        })->addMessage(OptionForm::AUTO_DETECT)->handle();

        return view('admin.customize', ['forms' => compact('homepage', 'customJsCss')]);
    }

    public function score()
    {
        $rate = Option::form('rate', OptionForm::AUTO_DETECT, function($form)
        {
            $form->group('score_per_storage')->text('score_per_storage')->addon(OptionForm::AUTO_DETECT);

            $form->group('private_score_per_storage')
                ->text('private_score_per_storage')->addon(OptionForm::AUTO_DETECT)
                ->hint(OptionForm::AUTO_DETECT);

            $form->group('score_per_closet_item')
                ->text('score_per_closet_item')->addon(OptionForm::AUTO_DETECT);

            $form->checkbox('return_score')->label(OptionForm::AUTO_DETECT);

            $form->group('score_per_player')->text('score_per_player')->addon(OptionForm::AUTO_DETECT);

            $form->text('user_initial_score');

        })->handle();

        $signIn = Option::form('sign_in', OptionForm::AUTO_DETECT, function($form)
        {
            $form->group('sign_score')
                ->text('sign_score_from')->addon(trans('options.sign_score.addon1'))
                ->text('sign_score_to')->addon(trans('options.sign_score.addon2'));

            $form->group('sign_gap_time')->text('sign_gap_time')->addon(OptionForm::AUTO_DETECT);

            $form->checkbox('sign_after_zero')->label(OptionForm::AUTO_DETECT)
                ->hint(OptionForm::AUTO_DETECT);
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
        $general = Option::form('general', OptionForm::AUTO_DETECT, function($form)
        {
            $form->text('site_name');
            $form->text('site_description');
            $form->text('site_url')->hint(OptionForm::AUTO_DETECT);

            $form->checkbox('user_can_register')->label(OptionForm::AUTO_DETECT);

            $form->text('regs_per_ip');

            $form->group('max_upload_file_size')
                    ->text('max_upload_file_size')->addon('KB')
                    ->hint(trans('options.max_upload_file_size.hint', ['size' => ini_get('upload_max_filesize')]));

            $form->checkbox('allow_chinese_playername')->label(OptionForm::AUTO_DETECT);

            $form->select('api_type')
                    ->option('0', 'CustomSkinLoader API')
                    ->option('1', 'UniversalSkinAPI');

            $form->checkbox('auto_del_invalid_texture')->label(OptionForm::AUTO_DETECT)->hint(OptionForm::AUTO_DETECT);

            $form->textarea('comment_script')->rows(6)->description(OptionForm::AUTO_DETECT);

            $form->checkbox('allow_sending_statistic')->label(OptionForm::AUTO_DETECT)->hint(OptionForm::AUTO_DETECT);

        })->handle(function() {
            if (substr($_POST['site_url'], -1) == "/")
                $_POST['site_url'] = substr($_POST['site_url'], 0, -1);
        });

        $announ = Option::form('announ', OptionForm::AUTO_DETECT, function($form)
        {
            $form->textarea('announcement')->description(OptionForm::AUTO_DETECT);

        })->renderWithOutTable()->handle();

        $cache = Option::form('cache', OptionForm::AUTO_DETECT, function($form)
        {
            $form->checkbox('force_ssl')->label(OptionForm::AUTO_DETECT)->hint(OptionForm::AUTO_DETECT);
            $form->checkbox('auto_detect_asset_url')->label(OptionForm::AUTO_DETECT)->description(OptionForm::AUTO_DETECT);
            $form->checkbox('return_200_when_notfound')->label(OptionForm::AUTO_DETECT);

            $form->text('cache_expire_time')->hint(OptionForm::AUTO_DETECT);

        })->type('warning')->hint(OptionForm::AUTO_DETECT)->handle();

        return view('admin.options')->with('forms', compact('general', 'cache', 'announ'));
    }

    /**
     * Show Manage Page of Users.
     *
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function users(Request $request)
    {
        return view('admin.users');
    }

    public function getUserData()
    {
        $users = User::select(['uid', 'email', 'nickname', 'score', 'permission', 'register_at']);

        $permissionTextMap = [
            User::BANNED => trans('admin.users.status.banned'),
            User::NORMAL => trans('admin.users.status.normal'),
            User::ADMIN  => trans('admin.users.status.admin'),
            User::SUPER_ADMIN => trans('admin.users.status.super-admin')
        ];

        return Datatables::of($users)->editColumn('email', function ($user) {
            return $user->email ?: 'EMPTY';
        })->editColumn('permission', function ($user) use ($permissionTextMap) {
            return Arr::get($permissionTextMap, $user->permission);
        })
        ->setRowId('uid')
        ->editColumn('score', 'vendor.admin-operations.users.score')
        ->addColumn('operations', 'vendor.admin-operations.users.operations')
        ->make(true);
    }

    /**
     * Show Manage Page of Players.
     *
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function players(Request $request)
    {
        return view('admin.players');
    }

    public function getPlayerData()
    {
        $players = Player::select(['pid', 'uid', 'player_name', 'preference', 'tid_steve', 'tid_alex', 'tid_cape', 'last_modified']);

        return Datatables::of($players)->editColumn('preference', 'vendor.admin-operations.players.preference')
            ->setRowId('pid')
            ->addColumn('previews', 'vendor.admin-operations.players.previews')
            ->addColumn('operations', 'vendor.admin-operations.players.operations')
            ->make(true);
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
        $user   = $users->get($request->input('uid'));

        if (!$user)
            return json(trans('admin.users.operations.non-existent'), 1);

        if ($action == "email") {
            $this->validate($request, [
                'email' => 'required|email'
            ]);

            $user->setEmail($request->input('email'));

            return json(trans('admin.users.operations.email.success'), 0);

        } elseif ($action == "nickname") {
            $this->validate($request, [
                'nickname' => 'required|nickname'
            ]);

            $user->setNickName($request->input('nickname'));

            return json(trans('admin.users.operations.nickname.success', ['new' => $request->input('nickname')]), 0);

        } elseif ($action == "password") {
            $this->validate($request, [
                'password' => 'required|min:8|max:16'
            ]);

            $user->changePasswd($request->input('password'));

            return json(trans('admin.users.operations.password.success'), 0);

        } elseif ($action == "score") {
            $this->validate($request, [
                'score' => 'required|integer'
            ]);

            $user->setScore($request->input('score'));

            return json(trans('admin.users.operations.score.success'), 0);

        } elseif ($action == "ban") {
            if ($user->getPermission() == User::ADMIN) {
                if (app('user.current')->getPermission() != User::SUPER_ADMIN)
                    return json(trans('admin.users.operations.ban.cant-admin'));
            } elseif ($user->getPermission() == User::SUPER_ADMIN) {
                return json(trans('admin.users.operations.ban.cant-super-admin'));
            }

            $permission = $user->getPermission() == User::BANNED ? User::NORMAL : User::BANNED;

            $user->setPermission($permission);

            return json([
                'errno'      => 0,
                'msg'        => trans('admin.users.operations.ban.'.($permission == User::BANNED ? 'ban' : 'unban').'.success'),
                'permission' => $user->getPermission()
            ]);

        } elseif ($action == "admin") {
            if (app('user.current')->getPermission() != User::SUPER_ADMIN)
                return json(trans('admin.users.operations.admin.cant-set'));

            if ($user->getPermission() == User::SUPER_ADMIN)
                return json(trans('admin.users.operations.admin.cant-unset'));

            $permission = $user->getPermission() == User::ADMIN ? User::NORMAL : User::ADMIN;

            $user->setPermission($permission);

            return json([
                'errno'      => 0,
                'msg'        => trans('admin.users.operations.admin.'.($permission == User::ADMIN ? 'set' : 'unset').'.success'),
                'permission' => $user->getPermission()
            ]);

        } elseif ($action == "delete") {
            $user->delete();

            return json(trans('admin.users.operations.delete.success'), 0);
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

            $player->setPreference($request->input('preference'));

            return json(trans('admin.players.preference.success', ['player' => $player->player_name, 'preference' => $request->input('preference')]), 0);

        } elseif ($action == "texture") {
            $this->validate($request, [
                'model' => 'required|model',
                'tid'   => 'required|integer'
            ]);

            if (!Texture::find($request->tid))
                return json(trans('admin.players.textures.non-existent', ['tid' => $request->tid]), 1);

            $player->setTexture(['tid_'.$request->model => $request->tid]);

            return json(trans('admin.players.textures.success', ['player' => $player->player_name]), 0);

        } elseif ($action == "owner") {
            $this->validate($request, [
                'pid'   => 'required|integer',
                'uid'   => 'required|integer'
            ]);

            $user = $users->get($request->input('uid'));

            if (!$user)
                return json(trans('admin.users.operations.non-existent'), 1);

            $player->setOwner($request->input('uid'));

            return json(trans('admin.players.owner.success', ['player' => $player->player_name, 'user' => $user->getNickName()]), 0);

        } elseif ($action == "delete") {
            $player->delete();

            return json(trans('admin.players.delete.success'), 0);
        }
    }

}

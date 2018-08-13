<?php

namespace App\Http\Controllers;

use Option;
use Datatables;
use App\Events;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Player;
use App\Models\Texture;
use Illuminate\Http\Request;
use App\Services\OptionForm;
use Illuminate\Support\Facades\Auth;
use App\Services\Repositories\UserRepository;

class AdminController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->timestamp;

        // Prepare data for the graph
        $data   = [];
        $labels = [];

        for ($i = 6; $i >= 0; $i--) {
            $time = Carbon::createFromTimestamp($today - $i * 86400);

            $labels[] = $time->format('m-d');
            $data['user_registration'][] = User::like('register_at',  $time->toDateString())->count();
            $data['texture_uploads'][]   = Texture::like('upload_at', $time->toDateString())->count();
        }

        $datasets = [
            [
                'label' => trans('admin.index.user-registration'),
                'backgroundColor' => 'rgba(60, 141, 188, 0.6)',
                'borderColor' => '#3c8dbc',
                'pointRadius' => 0,
                'pointBorderColor' => '#3c8dbc',
                'pointBackgroundColor' => '#3c8dbc',
                'pointHoverBackgroundColor' => '#3c8dbc',
                'pointHoverBorderColor' => '#3c8dbc',
                'data' => $data['user_registration'],
            ],
            [
                'label' => trans('admin.index.texture-uploads'),
                'backgroundColor' => 'rgba(210, 214, 222, 0.6)',
                'borderColor' => '#d2d6de',
                'pointRadius' => 0,
                'pointBorderColor' => '#c1c7d1',
                'pointBackgroundColor' => '#c1c7d1',
                'pointHoverBackgroundColor' => '#c1c7d1',
                'pointHoverBorderColor' => '#c1c7d1',
                'data' => $data['texture_uploads'],
            ]
        ];

        $options = [
            'tooltips' => [
                'intersect' => false,
                'mode' => 'index'
            ]
        ];

        return view('admin.index', ['chartOptions' => compact('labels', 'datasets', 'options')]);
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
            $form->text('home_pic_url')->hint();

            $form->text('favicon_url')->hint()->description();

            $form->select('copyright_prefer')
                    ->option('0', 'Powered with ❤ by Blessing Skin Server.')
                    ->option('1', 'Powered by Blessing Skin Server.')
                    ->option('2', 'Proudly powered by Blessing Skin Server.')
                    ->option('3', '由 Blessing Skin Server 强力驱动.')
                    ->option('4', '自豪地采用 Blessing Skin Server.')
                ->description();

            $form->textarea('copyright_text')->rows(6)->description();

        })->handle(function () {
            Option::set('copyright_prefer_'.config('app.locale'), request('copyright_prefer'));
            Option::set('copyright_text_'.config('app.locale'), request('copyright_text'));
        });

        $customJsCss = Option::form('customJsCss', OptionForm::AUTO_DETECT, function($form)
        {
            $form->textarea('custom_css', 'CSS')->rows(6);
            $form->textarea('custom_js', 'JavaScript')->rows(6);
        })->addMessage()->handle();

        return view('admin.customize', ['forms' => compact('homepage', 'customJsCss')]);
    }

    public function score()
    {
        $rate = Option::form('rate', OptionForm::AUTO_DETECT, function($form)
        {
            $form->group('score_per_storage')->text('score_per_storage')->addon();

            $form->group('private_score_per_storage')
                ->text('private_score_per_storage')->addon()->hint();

            $form->group('score_per_closet_item')
                ->text('score_per_closet_item')->addon();

            $form->checkbox('return_score')->label();

            $form->group('score_per_player')->text('score_per_player')->addon();

            $form->text('user_initial_score');

        })->handle();

        $sign = Option::form('sign', OptionForm::AUTO_DETECT, function($form)
        {
            $form->group('sign_score')
                ->text('sign_score_from')->addon(trans('options.sign.sign_score.addon1'))
                ->text('sign_score_to')->addon(trans('options.sign.sign_score.addon2'));

            $form->group('sign_gap_time')->text('sign_gap_time')->addon();

            $form->checkbox('sign_after_zero')->label()->hint();
        })->after(function() {
            $sign_score = request('sign_score_from').','.request('sign_score_to');
            Option::set('sign_score', $sign_score);
        })->with([
            'sign_score_from' => @explode(',', option('sign_score'))[0],
            'sign_score_to'   => @explode(',', option('sign_score'))[1]
        ])->handle();

        return view('admin.score', ['forms' => compact('rate', 'sign')]);
    }

    public function options()
    {
        $general = Option::form('general', OptionForm::AUTO_DETECT, function($form)
        {
            $form->text('site_name');
            $form->text('site_description')->description();

            $form->text('site_url')
                ->hint()
                ->format(function ($url) {
                    if (ends_with($url, '/')) {
                        $url = substr($url, 0, -1);
                    }

                    if (ends_with($url, '/index.php')) {
                        $url = substr($url, 0, -10);
                    }

                    return $url;
                });

            $form->checkbox('user_can_register')->label();

            $form->text('regs_per_ip');

            $form->select('ip_get_method')
                    ->option('0', trans('options.general.ip_get_method.HTTP_X_FORWARDED_FOR'))
                    ->option('1', trans('options.general.ip_get_method.REMOTE_ADDR'))
                    ->hint();

            $form->group('max_upload_file_size')
                    ->text('max_upload_file_size')->addon('KB')
                    ->hint(trans('options.general.max_upload_file_size.hint', ['size' => ini_get('upload_max_filesize')]));

            $form->select('player_name_rule')
                    ->option('official', trans('options.general.player_name_rule.official'))
                    ->option('cjk', trans('options.general.player_name_rule.cjk'))
                    ->option('custom', trans('options.general.player_name_rule.custom'));

            $form->text('custom_player_name_regexp')->hint()->placeholder();

            $form->group('player_name_length')
                ->addon(trans('options.general.player_name_length.addon1'))
                ->text('player_name_length_min')
                ->addon(trans('options.general.player_name_length.addon2'))
                ->text('player_name_length_max')
                ->addon(trans('options.general.player_name_length.addon3'));

            $form->select('api_type')
                    ->option('0', 'CustomSkinLoader API')
                    ->option('1', 'UniversalSkinAPI');

            $form->checkbox('auto_del_invalid_texture')->label()->hint();

            $form->checkbox('allow_downloading_texture')->label();

            $form->text('texture_name_regexp')->hint()->placeholder();

            $form->textarea('comment_script')->rows(6)->description();

            $form->checkbox('allow_sending_statistics')->label()->hint();

        })->handle(function () {
            Option::set('site_name_'.config('app.locale'), request('site_name'));
            Option::set('site_description_'.config('app.locale'), request('site_description'));
        });

        $announ = Option::form('announ', OptionForm::AUTO_DETECT, function ($form) {
            $form->textarea('announcement')->rows(10)->description();
        })->renderWithOutTable()->handle(function () {
            Option::set('announcement_'.config('app.locale'), request('announcement'));
        });

        $resources = Option::form('resources', OptionForm::AUTO_DETECT, function($form)
        {
            $form->checkbox('force_ssl')->label()->hint();
            $form->checkbox('auto_detect_asset_url')->label()->description();
            $form->checkbox('return_204_when_notfound')->label()->description();

            $form->text('cache_expire_time')->hint(OptionForm::AUTO_DETECT);

        })->type('warning')->hint(OptionForm::AUTO_DETECT)->handle();

        return view('admin.options')->with('forms', compact('general', 'resources', 'announ'));
    }

    public function getUserData(Request $request)
    {
        $isSingleUser = $request->has('uid');

        if ($isSingleUser) {
            $users = User::select(['uid', 'email', 'nickname', 'score', 'permission', 'register_at'])
                        ->where('uid', intval($request->input('uid')))
                        ->get();
        } else {
            $search = $request->input('search', '');
            $sortField = $request->input('sortField', 'uid');
            $sortType = $request->input('sortType', 'asc');
            $page = $request->input('page', 1);
            $perPage = $request->input('perPage', 10);

            $users = User::select(['uid', 'email', 'nickname', 'score', 'permission', 'register_at'])
                        ->where('uid', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('nickname', 'like', '%' . $search . '%')
                        ->orWhere('score', 'like', '%' . $search . '%')
                        ->orderBy($sortField, $sortType)
                        ->offset(($page - 1) * $perPage)
                        ->limit($perPage)
                        ->get();
        }

        $users->transform(function ($user) {
            $user->operations = auth()->user()->permission;
            $user->players_count = $user->players->count();
            return $user;
        });

        return [
            'totalRecords' => $isSingleUser ? 1 : User::count(),
            'data' => $users
        ];
    }

    public function getPlayerData(Request $request)
    {
        $isSpecifiedUser = $request->has('uid');

        if ($isSpecifiedUser) {
            $players = Player::select(['pid', 'uid', 'player_name', 'preference', 'tid_steve', 'tid_alex', 'tid_cape', 'last_modified'])
                            ->where('uid', intval($request->input('uid')))
                            ->get();
        } else {
            $search = $request->input('search', '');
            $sortField = $request->input('sortField', 'pid');
            $sortType = $request->input('sortType', 'asc');
            $page = $request->input('page', 1);
            $perPage = $request->input('perPage', 10);

            $players = Player::select(['pid', 'uid', 'player_name', 'preference', 'tid_steve', 'tid_alex', 'tid_cape', 'last_modified'])
                            ->where('pid', 'like', '%' . $search . '%')
                            ->orWhere('uid', 'like', '%' . $search . '%')
                            ->orWhere('player_name', 'like', '%' . $search . '%')
                            ->orWhere('preference', 'like', '%' . $search . '%')
                            ->orderBy($sortField, $sortType)
                            ->offset(($page - 1) * $perPage)
                            ->limit($perPage)
                            ->get();
        }

        return [
            'totalRecords' => $isSpecifiedUser ? 1 : Player::count(),
            'data' => $players
        ];
    }

    /**
     * Handle ajax request from /admin/users
     *
     * @param  Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userAjaxHandler(Request $request, UserRepository $users)
    {
        $action = $request->input('action');
        $user = $users->get($request->input('uid'));
        $currentUser = Auth::user();

        if (! $user) {
            return json(trans('admin.users.operations.non-existent'), 1);
        }

        if ($user->uid !== $currentUser->uid) {
            if ($user->permission >= $currentUser->permission) {
                return json(trans('admin.users.operations.no-permission'), 1);
            }
        }

        if ($action == "email") {
            $this->validate($request, [
                'email' => 'required|email'
            ]);

            if ($users->get($request->input('email'), 'email')) {
                return json(trans('admin.users.operations.email.existed', ['email' => $request->input('email')]), 1);
            }

            $user->setEmail($request->input('email'));

            return json(trans('admin.users.operations.email.success'), 0);

        } elseif ($action == "nickname") {
            $this->validate($request, [
                'nickname' => 'required|no_special_chars'
            ]);

            $user->setNickName($request->input('nickname'));

            return json(trans('admin.users.operations.nickname.success', [
                'new' => $request->input('nickname')
            ]), 0);

        } elseif ($action == "password") {
            $this->validate($request, [
                'password' => 'required|min:8|max:16'
            ]);

            $user->changePassword($request->input('password'));

            return json(trans('admin.users.operations.password.success'), 0);

        } elseif ($action == "score") {
            $this->validate($request, [
                'score' => 'required|integer'
            ]);

            $user->setScore($request->input('score'));

            return json(trans('admin.users.operations.score.success'), 0);

        } elseif ($action == "ban") {
            $permission = $user->getPermission() == User::BANNED ? User::NORMAL : User::BANNED;

            $user->setPermission($permission);

            return json([
                'errno'      => 0,
                'msg'        => trans('admin.users.operations.ban.'.($permission == User::BANNED ? 'ban' : 'unban').'.success'),
                'permission' => $user->getPermission()
            ]);

        } elseif ($action == "admin") {
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
        } else {
            return json(trans('admin.users.operations.invalid'), 1);
        }
    }

    /**
     * Handle ajax request from /admin/players
     */
    public function playerAjaxHandler(Request $request, UserRepository $users)
    {
        $action = $request->input('action');
        $currentUser = Auth::user();
        $player = Player::find($request->input('pid'));

        if (! $player) {
            return json(trans('general.unexistent-player'), 1);
        }

        if ($player->user()->first()->uid !== $currentUser->uid) {
            if ($player->user->permission >= $currentUser->permission) {
                return json(trans('admin.players.no-permission'), 1);
            }
        }

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

            if (! Texture::find($request->tid) && $request->tid != 0)
                return json(trans('admin.players.textures.non-existent', ['tid' => $request->tid]), 1);

            $player->setTexture(['tid_'.$request->model => $request->tid]);

            return json(trans('admin.players.textures.success', ['player' => $player->player_name]), 0);

        } elseif ($action == "owner") {
            $this->validate($request, [
                'uid'   => 'required|integer'
            ]);

            $user = $users->get($request->input('uid'));

            if (! $user)
                return json(trans('admin.users.operations.non-existent'), 1);

            $player->setOwner($request->input('uid'));

            return json(trans('admin.players.owner.success', ['player' => $player->player_name, 'user' => $user->getNickName()]), 0);

        } elseif ($action == "delete") {
            $player->delete();

            return json(trans('admin.players.delete.success'), 0);
        } elseif ($action == "name") {
            $this->validate($request, [
                'name' => 'required'
            ]);

            $player->rename($request->input('name'));

            return json(trans('admin.players.name.success', ['player' => $player->player_name]), 0, ['name' => $player->player_name]);
        } else {
            return json(trans('admin.users.operations.invalid'), 1);
        }
    }

    /**
     * Get one user information
     *
     * @param  string $uid
     * @param  UserRepository $users
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOneUser($uid, UserRepository $users)
    {
        $user = $users->get(intval($uid));
        if ($user) {
            return json('success', 0, ['user' => $user->makeHidden([
                'password', 'ip', 'last_sign_at', 'register_at', 'remember_token'
            ])->toArray()]);
        } else {
            return json('No such user.', 1);
        }
    }

}

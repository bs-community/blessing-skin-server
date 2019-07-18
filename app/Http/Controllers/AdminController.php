<?php

namespace App\Http\Controllers;

use Cache;
use Option;
use Notification;
use Carbon\Carbon;
use App\Models\User;
use App\Notifications;
use App\Models\Player;
use App\Models\Texture;
use Illuminate\Support\Str;
use App\Services\OptionForm;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class AdminController extends Controller
{
    public function chartData()
    {
        $today = Carbon::today()->timestamp;

        $xAxis = Collection::times(31, function ($number) use ($today) {
            $time = Carbon::createFromTimestamp($today - (31 - $number) * 86400);

            return $time->format('m-d');
        });

        $oneMonthAgo = Carbon::createFromTimestamp($today - 30 * 86400);

        $grouping = function ($field) {
            return function ($item) use ($field) {
                return substr($item->$field, 5, 5);
            };
        };
        $mapping = function ($item) {
            return count($item);
        };
        $aligning = function ($data) {
            return function ($day) use ($data) {
                return $data->get($day) ?? 0;
            };
        };

        $userRegistration = User::where('register_at', '>=', $oneMonthAgo)
            ->select('register_at')
            ->get()
            ->groupBy($grouping('register_at'))
            ->map($mapping);

        $textureUploads = Texture::where('upload_at', '>=', $oneMonthAgo)
            ->select('upload_at')
            ->get()
            ->groupBy($grouping('upload_at'))
            ->map($mapping);

        return [
            'labels' => [
                trans('admin.index.user-registration'),
                trans('admin.index.texture-uploads'),
            ],
            'xAxis' => $xAxis,
            'data' => [
                $xAxis->map($aligning($userRegistration)),
                $xAxis->map($aligning($textureUploads)),
            ],
        ];
    }

    public function sendNotification(Request $request)
    {
        $data = $this->validate($request, [
            'receiver' => 'required|in:all,normal,uid,email',
            'uid' => 'required_if:receiver,uid|nullable|integer|exists:users',
            'email' => 'required_if:receiver,email|nullable|email|exists:users',
            'title' => 'required|max:20',
            'content' => 'string|nullable',
        ]);

        $notification = new Notifications\SiteMessage($data['title'], $data['content']);

        switch ($data['receiver']) {
            case 'all':
                $users = User::all();
                break;
            case 'normal':
                $users = User::where('permission', User::NORMAL)->get();
                break;
            case 'uid':
                $users = User::where('uid', $data['uid'])->get();
                break;
            case 'email':
                $users = User::where('email', $data['email'])->get();
                break;
        }
        Notification::send($users, $notification);

        session(['sentResult' => trans('admin.notifications.send.success')]);
        return redirect('/admin');
    }

    public function customize(Request $request)
    {
        $homepage = Option::form('homepage', OptionForm::AUTO_DETECT, function ($form) {
            $form->text('home_pic_url')->hint();

            $form->text('favicon_url')->hint()->description();

            $form->checkbox('transparent_navbar')->label();

            $form->checkbox('hide_intro')->label();

            $form->checkbox('fixed_bg')->label();

            $form->checkbox('hide_pray_for_kyoto_animation')->label();

            $form->select('copyright_prefer')
                    ->option('0', 'Powered with ❤ by Blessing Skin Server.')
                    ->option('1', 'Powered by Blessing Skin Server.')
                    ->option('2', 'Proudly powered by Blessing Skin Server.')
                    ->option('3', '由 Blessing Skin Server 强力驱动。')
                    ->option('4', '自豪地采用 Blessing Skin Server。')
                ->description();

            $form->textarea('copyright_text')->rows(6)->description();
        })->handle(function () {
            Option::set('copyright_prefer_'.config('app.locale'), request('copyright_prefer'));
            Option::set('copyright_text_'.config('app.locale'), request('copyright_text'));
        });

        $customJsCss = Option::form('customJsCss', OptionForm::AUTO_DETECT, function ($form) {
            $form->textarea('custom_css', 'CSS')->rows(6);
            $form->textarea('custom_js', 'JavaScript')->rows(6);
        })->addMessage()->handle();

        if ($request->isMethod('post') && $request->input('action') == 'color') {
            $color = $this->validate($request, ['color' => 'required'])['color'];
            option(['color_scheme' => $color]);
        }

        return view('admin.customize', ['forms' => compact('homepage', 'customJsCss')]);
    }

    public function score()
    {
        $rate = Option::form('rate', OptionForm::AUTO_DETECT, function ($form) {
            $form->group('score_per_storage')->text('score_per_storage')->addon();

            $form->group('private_score_per_storage')
                ->text('private_score_per_storage')->addon()->hint();

            $form->group('score_per_closet_item')
                ->text('score_per_closet_item')->addon();

            $form->checkbox('return_score')->label();

            $form->group('score_per_player')->text('score_per_player')->addon();

            $form->text('user_initial_score');
        })->handle();

        $report = Option::form('report', OptionForm::AUTO_DETECT, function ($form) {
            $form->text('reporter_score_modification')->description();

            $form->text('reporter_reward_score');
        })->handle();

        $sign = Option::form('sign', OptionForm::AUTO_DETECT, function ($form) {
            $form->group('sign_score')
                ->text('sign_score_from')->addon(trans('options.sign.sign_score.addon1'))
                ->text('sign_score_to')->addon(trans('options.sign.sign_score.addon2'));

            $form->group('sign_gap_time')->text('sign_gap_time')->addon();

            $form->checkbox('sign_after_zero')->label()->hint();
        })->after(function () {
            $sign_score = request('sign_score_from').','.request('sign_score_to');
            Option::set('sign_score', $sign_score);
        })->with([
            'sign_score_from' => @explode(',', option('sign_score'))[0],
            'sign_score_to'   => @explode(',', option('sign_score'))[1],
        ])->handle();

        $sharing = Option::form('sharing', OptionForm::AUTO_DETECT, function ($form) {
            $form->group('score_award_per_texture')
                ->text('score_award_per_texture')
                ->addon(trans('general.user.score'));
            $form->checkbox('take_back_scores_after_deletion')->label();
            $form->group('score_award_per_like')
                ->text('score_award_per_like')
                ->addon(trans('general.user.score'));
        })->handle();

        return view('admin.score', ['forms' => compact('rate', 'report', 'sign', 'sharing')]);
    }

    public function options()
    {
        $general = Option::form('general', OptionForm::AUTO_DETECT, function ($form) {
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
            $form->checkbox('register_with_player_name')->label();
            $form->checkbox('require_verification')->label();

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
                ->text('player_name_length_min')
                ->addon('~')
                ->text('player_name_length_max')
                ->addon(trans('options.general.player_name_length.suffix'));

            $form->select('api_type')
                    ->option('0', 'CustomSkinLoader API')
                    ->option('1', 'UniversalSkinAPI');

            $form->checkbox('auto_del_invalid_texture')->label()->hint();

            $form->checkbox('allow_downloading_texture')->label();

            $form->select('status_code_for_private')
                ->option('403', '403 Forbidden')
                ->option('404', '404 Not Found');

            $form->text('texture_name_regexp')->hint()->placeholder();

            $form->textarea('content_policy')->rows(3)->description();

            $form->textarea('comment_script')->rows(6)->description();
        })->handle(function () {
            Option::set('site_name_'.config('app.locale'), request('site_name'));
            Option::set('site_description_'.config('app.locale'), request('site_description'));
            Option::set('content_policy_'.config('app.locale'), request('content_policy'));
        });

        $announ = Option::form('announ', OptionForm::AUTO_DETECT, function ($form) {
            $form->textarea('announcement')->rows(10)->description();
        })->renderWithOutTable()->handle(function () {
            Option::set('announcement_'.config('app.locale'), request('announcement'));
        });

        $meta = Option::form('meta', OptionForm::AUTO_DETECT, function ($form) {
            $form->text('meta_keywords')->hint();
            $form->text('meta_description')->hint();
            $form->textarea('meta_extras')->rows(6);
        })->handle();

        $recaptcha = Option::form('recaptcha', 'reCAPTCHA', function ($form) {
            $form->text('recaptcha_sitekey', 'sitekey');
            $form->text('recaptcha_secretkey', 'secretkey');
            $form->checkbox('recaptcha_invisible')->label();
        })->handle();

        return view('admin.options')
            ->with('forms', compact('general', 'announ', 'meta', 'recaptcha'));
    }

    public function resource(Request $request)
    {
        $resources = Option::form('resources', OptionForm::AUTO_DETECT, function ($form) {
            $form->checkbox('force_ssl')->label()->hint();
            $form->checkbox('auto_detect_asset_url')->label()->description();
            $form->checkbox('return_204_when_notfound')->label()->description();

            $form->text('cache_expire_time')->hint(OptionForm::AUTO_DETECT);
            $form->text('cdn_address')
                ->hint(OptionForm::AUTO_DETECT)
                ->description(OptionForm::AUTO_DETECT);
        })
            ->type('primary')
            ->hint(OptionForm::AUTO_DETECT)
            ->after(function () {
                $cdnAddress = request('cdn_address');
                if ($cdnAddress == null) {
                    $cdnAddress = '';
                }
                if (Str::endsWith($cdnAddress, '/')) {
                    $cdnAddress = substr($cdnAddress, 0, -1);
                }
                Option::set('cdn_address', $cdnAddress);
            })
            ->handle();

        $redis = Option::form('redis', 'Redis', function ($form) {
            $form->checkbox('enable_redis')->label()->description();
        });

        if (option('enable_redis')) {
            try {
                Redis::ping();
                $redis->addMessage(trans('options.redis.connect.success'), 'success');
            } catch (\Exception $e) {
                $redis->addMessage(
                    trans('options.redis.connect.failed', ['msg' => $e->getMessage()]),
                    'danger'
                );
            }
        }

        $redis->handle();

        $cache = Option::form('cache', OptionForm::AUTO_DETECT, function ($form) {
            $form->checkbox('enable_avatar_cache')->label();
            $form->checkbox('enable_preview_cache')->label();
            $form->checkbox('enable_json_cache', 'JSON Profile')->label();
            $form->checkbox('enable_notfound_cache', '404')->label();
        })
            ->type('warning')
            ->addButton([
                'text' => trans('options.cache.clear'),
                'type' => 'a',
                'class' => 'pull-right',
                'style' => 'warning',
                'href' => '?clear-cache',
            ])
            ->addMessage(trans('options.cache.driver', ['driver' => config('cache.default')]), 'info');

        if ($request->has('clear-cache')) {
            Cache::flush();
            $cache->addMessage(trans('options.cache.cleared'), 'success');
        }
        $cache->handle();

        return view('admin.resource')
            ->with('forms', compact('resources', 'redis', 'cache'));
    }

    public function getUserData(Request $request)
    {
        $isSingleUser = $request->has('uid');

        if ($isSingleUser) {
            $users = User::select(['uid', 'email', 'nickname', 'score', 'permission', 'register_at', 'verified'])
                        ->where('uid', intval($request->input('uid')))
                        ->get();
        } else {
            $search = $request->input('search', '');
            $sortField = $request->input('sortField', 'uid');
            $sortType = $request->input('sortType', 'asc');
            $page = $request->input('page', 1);
            $perPage = $request->input('perPage', 10);

            $users = User::select(['uid', 'email', 'nickname', 'score', 'permission', 'register_at', 'verified'])
                        ->where('uid', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('nickname', 'like', '%'.$search.'%')
                        ->orWhere('score', 'like', '%'.$search.'%')
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
            'data' => $users,
        ];
    }

    public function getPlayerData(Request $request)
    {
        $isSpecifiedUser = $request->has('uid');

        if ($isSpecifiedUser) {
            $players = Player::select(['pid', 'uid', 'name', 'tid_skin', 'tid_cape', 'last_modified'])
                            ->where('uid', intval($request->input('uid')))
                            ->get();
        } else {
            $search = $request->input('search', '');
            $sortField = $request->input('sortField', 'pid');
            $sortType = $request->input('sortType', 'asc');
            $page = $request->input('page', 1);
            $perPage = $request->input('perPage', 10);

            $players = Player::select(['pid', 'uid', 'name', 'tid_skin', 'tid_cape', 'last_modified'])
                            ->where('pid', 'like', '%'.$search.'%')
                            ->orWhere('uid', 'like', '%'.$search.'%')
                            ->orWhere('name', 'like', '%'.$search.'%')
                            ->orderBy($sortField, $sortType)
                            ->offset(($page - 1) * $perPage)
                            ->limit($perPage)
                            ->get();
        }

        return [
            'totalRecords' => $isSpecifiedUser ? 1 : Player::count(),
            'data' => $players,
        ];
    }

    public function userAjaxHandler(Request $request)
    {
        $action = $request->input('action');
        $user = User::find($request->uid);
        $currentUser = Auth::user();

        if (! $user) {
            return json(trans('admin.users.operations.non-existent'), 1);
        }

        if ($user->uid !== $currentUser->uid && $user->permission >= $currentUser->permission) {
            return json(trans('admin.users.operations.no-permission'), 1);
        }

        if ($action == 'email') {
            $this->validate($request, [
                'email' => 'required|email',
            ]);

            if (User::where('email', $request->email)->count() != 0) {
                return json(trans('admin.users.operations.email.existed', ['email' => $request->input('email')]), 1);
            }

            $user->email = $request->input('email');
            $user->save();

            return json(trans('admin.users.operations.email.success'), 0);
        } elseif ($action == 'verification') {
            $user->verified = ! $user->verified;
            $user->save();

            return json(trans('admin.users.operations.verification.success'), 0);
        } elseif ($action == 'nickname') {
            $this->validate($request, [
                'nickname' => 'required|no_special_chars',
            ]);

            $user->nickname = $request->input('nickname');
            $user->save();

            return json(trans('admin.users.operations.nickname.success', [
                'new' => $request->input('nickname'),
            ]), 0);
        } elseif ($action == 'password') {
            $this->validate($request, [
                'password' => 'required|min:8|max:16',
            ]);

            $user->changePassword($request->input('password'));

            return json(trans('admin.users.operations.password.success'), 0);
        } elseif ($action == 'score') {
            $this->validate($request, [
                'score' => 'required|integer',
            ]);

            $user->setScore($request->input('score'));

            return json(trans('admin.users.operations.score.success'), 0);
        } elseif ($action == 'permission') {
            $user->permission = $this->validate($request, [
                'permission' => 'required|in:-1,0,1',
            ])['permission'];
            $user->save();

            return json([
                'code' => 0,
                'message' => trans('admin.users.operations.permission'),
            ]);
        } elseif ($action == 'delete') {
            $user->delete();

            return json(trans('admin.users.operations.delete.success'), 0);
        } else {
            return json(trans('admin.users.operations.invalid'), 1);
        }
    }

    public function playerAjaxHandler(Request $request)
    {
        $action = $request->input('action');
        $currentUser = Auth::user();
        $player = Player::find($request->input('pid'));

        if (! $player) {
            return json(trans('general.unexistent-player'), 1);
        }

        $owner = $player->user;
        if (
            $owner && $owner->uid !== $currentUser->uid &&
            $owner->permission >= $currentUser->permission
        ) {
            return json(trans('admin.players.no-permission'), 1);
        }

        if ($action == 'texture') {
            $this->validate($request, [
                'type' => 'required',
                'tid'  => 'required|integer',
            ]);

            if (! Texture::find($request->tid) && $request->tid != 0) {
                return json(trans('admin.players.textures.non-existent', ['tid' => $request->tid]), 1);
            }

            $field = 'tid_'.$request->type;
            $player->$field = $request->tid;
            $player->save();

            return json(trans('admin.players.textures.success', ['player' => $player->name]), 0);
        } elseif ($action == 'owner') {
            $this->validate($request, [
                'uid' => 'required|integer',
            ]);

            $user = User::find($request->uid);

            if (! $user) {
                return json(trans('admin.users.operations.non-existent'), 1);
            }

            $player->uid = $request->input('uid');
            $player->save();

            return json(trans('admin.players.owner.success', ['player' => $player->name, 'user' => $user->nickname]), 0);
        } elseif ($action == 'delete') {
            $player->delete();

            return json(trans('admin.players.delete.success'), 0);
        } elseif ($action == 'name') {
            $name = $this->validate($request, [
                'name' => 'required|player_name|min:'.option('player_name_length_min').'|max:'.option('player_name_length_max'),
            ])['name'];

            $player->name = $name;
            $player->save();

            if (option('single_player', false) && $owner) {
                $owner->nickname = $name;
                $owner->save();
            }

            return json(trans('admin.players.name.success', ['player' => $player->name]), 0);
        } else {
            return json(trans('admin.users.operations.invalid'), 1);
        }
    }
}

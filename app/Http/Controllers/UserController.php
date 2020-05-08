<?php

namespace App\Http\Controllers;

use App\Events\UserProfileUpdated;
use App\Mail\EmailVerification;
use App\Models\Texture;
use App\Models\User;
use Auth;
use Blessing\Filter;
use Blessing\Rejection;
use Carbon\Carbon;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use Mail;
use Parsedown;
use Session;
use URL;

class UserController extends Controller
{
    public function user()
    {
        $user = auth()
            ->user()
            ->makeHidden(['password', 'ip', 'remember_token', 'verification_token'])
            ->toArray();

        return json('', 0, $user);
    }

    public function index(Filter $filter)
    {
        $user = Auth::user();

        [$from, $to] = explode(',', option('sign_score'));
        $scoreIntro = trans('user.score-intro.introduction', [
            'initial_score' => option('user_initial_score'),
            'score-from' => $from,
            'score-to' => $to,
            'return-score' => option('return_score')
                ? trans('user.score-intro.will-return-score')
                : trans('user.score-intro.no-return-score'),
        ]);

        $grid = [
            'layout' => [
                ['md-7', 'md-5'],
            ],
            'widgets' => [
                [
                    [
                        'user.widgets.email-verification',
                        'user.widgets.dashboard.usage',
                    ],
                    ['user.widgets.dashboard.announcement'],
                ],
            ],
        ];
        $grid = $filter->apply('grid:user.index', $grid);

        $parsedown = new Parsedown();

        return view('user.index')->with([
            'statistics' => [
                'players' => $this->calculatePercentageUsed($user->players->count(), option('score_per_player')),
                'storage' => $this->calculatePercentageUsed($this->getStorageUsed($user), option('score_per_storage')),
            ],
            'score_intro' => $scoreIntro,
            'rates' => [
                'storage' => option('score_per_storage'),
                'player' => option('score_per_player'),
                'closet' => option('score_per_closet_item'),
            ],
            'announcement' => $parsedown->text(option_localized('announcement')),
            'grid' => $grid,
            'extra' => ['unverified' => option('require_verification') && !$user->verified],
        ]);
    }

    public function scoreInfo()
    {
        $user = Auth::user();

        return json('', 0, [
            'user' => [
                'score' => $user->score,
                'lastSignAt' => $user->last_sign_at,
            ],
            'stats' => [
                'players' => $this->calculatePercentageUsed($user->players->count(), option('score_per_player')),
                'storage' => $this->calculatePercentageUsed($this->getStorageUsed($user), option('score_per_storage')),
            ],
            'signAfterZero' => (bool) option('sign_after_zero'),
            'signGapTime' => (int) option('sign_gap_time'),
        ]);
    }

    protected function calculatePercentageUsed(int $used, int $rate): array
    {
        $user = Auth::user();
        // Initialize default value to avoid division by zero.
        $result['used'] = $used;
        $result['total'] = 'UNLIMITED';
        $result['percentage'] = 0;

        if ($rate != 0) {
            $result['total'] = $used + floor($user->score / $rate);
            $result['percentage'] = $result['total'] ? $used / $result['total'] * 100 : 100;
        }

        return $result;
    }

    protected function getStorageUsed(User $user)
    {
        return Texture::where('uploader', $user->uid)->select('size')->sum('size');
    }

    public function sign()
    {
        $user = Auth::user();
        if ($this->getSignRemainingTime($user) <= 0) {
            $acquiredScore = rand(...explode(',', option('sign_score')));
            $user->score += $acquiredScore;
            $user->last_sign_at = Carbon::now();
            $user->save();
            $gap = option('sign_gap_time');

            return json(trans('user.sign-success', ['score' => $acquiredScore]), 0, [
                'score' => $user->score,
                'storage' => $this->calculatePercentageUsed($this->getStorageUsed($user), option('score_per_storage')),
                'remaining_time' => $gap > 1 ? round($gap) : $gap,
            ]);
        } else {
            $remaining_time = $this->getUserSignRemainingTimeWithPrecision($user);

            return json(trans('user.cant-sign-until', [
                'time' => $remaining_time >= 1
                    ? $remaining_time : round($remaining_time * 60),
                'unit' => $remaining_time >= 1
                    ? trans('user.time-unit-hour') : trans('user.time-unit-min'),
            ]), 1);
        }
    }

    protected function getUserSignRemainingTimeWithPrecision(User $user)
    {
        $hours = $this->getSignRemainingTime($user) / 3600;

        return $hours > 1 ? round($hours) : $hours;
    }

    protected function getSignRemainingTime(User $user)
    {
        $lastSignTime = Carbon::parse($user->last_sign_at);

        if (option('sign_after_zero')) {
            return Carbon::now()->diffInSeconds(
                $lastSignTime <= Carbon::today() ? $lastSignTime : Carbon::tomorrow(),
                false
            );
        }

        return Carbon::now()->diffInSeconds($lastSignTime->addHours(option('sign_gap_time')), false);
    }

    public function sendVerificationEmail()
    {
        if (!option('require_verification')) {
            return json(trans('user.verification.disabled'), 1);
        }

        // Rate limit of 60s
        $remain = 60 + session('last_mail_time', 0) - time();

        if ($remain > 0) {
            return json(trans('user.verification.frequent-mail'), 1);
        }

        $user = Auth::user();

        if ($user->verified) {
            return json(trans('user.verification.verified'), 1);
        }

        $url = URL::signedRoute('auth.verify', ['uid' => $user->uid], null, false);

        try {
            Mail::to($user->email)->send(new EmailVerification(url($url)));
        } catch (\Exception $e) {
            // Write the exception to log
            report($e);

            return json(trans('user.verification.failed', ['msg' => $e->getMessage()]), 2);
        }

        Session::put('last_mail_time', time());

        return json(trans('user.verification.success'), 0);
    }

    public function profile(Filter $filter)
    {
        $user = Auth::user();

        $grid = [
            'layout' => [
                ['md-6', 'md-6'],
            ],
            'widgets' => [
                [
                    [
                        'user.widgets.profile.avatar',
                        'user.widgets.profile.password',
                    ],
                    [
                        'user.widgets.profile.nickname',
                        'user.widgets.profile.email',
                        'user.widgets.profile.delete-account',
                    ],
                ],
            ],
        ];
        $grid = $filter->apply('grid:user.profile', $grid);

        return view('user.profile')
            ->with('user', $user)
            ->with('grid', $grid)
            ->with('site_name', option_localized('site_name'));
    }

    public function handleProfile(Request $request, Filter $filter, Dispatcher $dispatcher)
    {
        $action = $request->input('action', '');
        $user = Auth::user();
        $addition = $request->except('action');

        $can = $filter->apply('user_can_edit_profile', true, [$action, $addition]);
        if ($can instanceof Rejection) {
            return json($can->getReason(), 1);
        }

        $dispatcher->dispatch('user.profile.updating', [$user, $action, $addition]);

        switch ($action) {
            case 'nickname':
                if (option('single_player', false)) {
                    return json(trans('user.profile.nickname.single'), 1);
                }

                $this->validate($request, ['new_nickname' => 'required']);

                $nickname = $request->input('new_nickname');
                $user->nickname = $nickname;
                $user->save();

                $dispatcher->dispatch('user.profile.updated', [$user, $action, $addition]);
                event(new UserProfileUpdated($action, $user));

                return json(trans('user.profile.nickname.success', ['nickname' => $nickname]), 0);

            case 'password':
                $this->validate($request, [
                    'current_password' => 'required|min:6|max:32',
                    'new_password' => 'required|min:8|max:32',
                ]);

                if (!$user->verifyPassword($request->input('current_password'))) {
                    return json(trans('user.profile.password.wrong-password'), 1);
                }

                $user->changePassword($request->input('new_password'));
                $dispatcher->dispatch('user.profile.updated', [$user, $action, $addition]);
                event(new UserProfileUpdated($action, $user));

                Auth::logout();

                return json(trans('user.profile.password.success'), 0);

            case 'email':
                $this->validate($request, [
                    'new_email' => 'required|email',
                    'password' => 'required|min:6|max:32',
                ]);

                if (User::where('email', $request->new_email)->count() > 0) {
                    return json(trans('user.profile.email.existed'), 1);
                }

                if (!$user->verifyPassword($request->input('password'))) {
                    return json(trans('user.profile.email.wrong-password'), 1);
                }

                $user->email = $request->input('new_email');
                $user->verified = false;
                $user->save();

                $dispatcher->dispatch('user.profile.updated', [$user, $action, $addition]);
                event(new UserProfileUpdated($action, $user));

                Auth::logout();

                return json(trans('user.profile.email.success'), 0);

            case 'delete':
                $this->validate($request, [
                    'password' => 'required|min:6|max:32',
                ]);

                if ($user->isAdmin()) {
                    return json(trans('user.profile.delete.admin'), 1);
                }

                if (!$user->verifyPassword($request->input('password'))) {
                    return json(trans('user.profile.delete.wrong-password'), 1);
                }

                Auth::logout();

                $dispatcher->dispatch('user.deleting', [$user]);

                $user->delete();
                $dispatcher->dispatch('user.deleted', [$user]);
                session()->flush();

                return json(trans('user.profile.delete.success'), 0);

            default:
                return json(trans('general.illegal-parameters'), 1);
        }
    }

    public function setAvatar(Request $request, Filter $filter, Dispatcher $dispatcher)
    {
        $this->validate($request, ['tid' => 'required|integer']);
        $tid = $request->input('tid');
        $user = auth()->user();

        $can = $filter->apply('user_can_update_avatar', true, [$user, $tid]);
        if ($can instanceof Rejection) {
            return json($can->getReason(), 1);
        }

        $dispatcher->dispatch('user.avatar.updating', [$user, $tid]);

        if ($tid == 0) {
            $user->avatar = 0;
            $user->save();

            $dispatcher->dispatch('user.avatar.updated', [$user, $tid]);

            return json(trans('user.profile.avatar.success'), 0);
        }

        $texture = Texture::find($tid);
        if ($texture) {
            if ($texture->type == 'cape') {
                return json(trans('user.profile.avatar.wrong-type'), 1);
            }

            $user->avatar = $tid;
            $user->save();

            $dispatcher->dispatch('user.avatar.updated', [$user, $tid]);

            return json(trans('user.profile.avatar.success'), 0);
        } else {
            return json(trans('skinlib.non-existent'), 1);
        }
    }
}

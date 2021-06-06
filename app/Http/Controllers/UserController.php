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
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Mail;
use Session;
use URL;

class UserController extends Controller
{
    public function user()
    {
        /** @var User */
        $user = auth()->user();

        return $user
            ->makeHidden(['password', 'ip', 'remember_token', 'verification_token']);
    }

    public function index(Filter $filter)
    {
        $user = Auth::user();

        [$min, $max] = explode(',', option('sign_score'));
        $scoreIntro = trans('user.score-intro.introduction', [
            'initial_score' => option('user_initial_score'),
            'score-from' => $min,
            'score-to' => $max,
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

        $converter = new GithubFlavoredMarkdownConverter();

        return view('user.index')->with([
            'score_intro' => $scoreIntro,
            'rates' => [
                'storage' => option('score_per_storage'),
                'player' => option('score_per_player'),
                'closet' => option('score_per_closet_item'),
            ],
            'announcement' => $converter->convertToHtml(option_localized('announcement')),
            'grid' => $grid,
            'extra' => ['unverified' => option('require_verification') && !$user->verified],
        ]);
    }

    public function scoreInfo()
    {
        /** @var User */
        $user = Auth::user();

        return response()->json([
            'user' => [
                'score' => $user->score,
                'lastSignAt' => $user->last_sign_at,
            ],
            'rate' => [
                'storage' => (int) option('score_per_storage'),
                'players' => (int) option('score_per_player'),
            ],
            'usage' => [
                'players' => $user->players()->count(),
                'storage' => (int) Texture::where('uploader', $user->uid)->sum('size'),
            ],
            'signAfterZero' => (bool) option('sign_after_zero'),
            'signGapTime' => (int) option('sign_gap_time'),
        ]);
    }

    public function sign(Dispatcher $dispatcher, Filter $filter)
    {
        /** @var User */
        $user = Auth::user();

        $can = $filter->apply('can_sign', true);
        if ($can instanceof Rejection) {
            return json($can->getReason(), 2);
        }

        $lastSignTime = Carbon::parse($user->last_sign_at);
        $remainingTime = option('sign_after_zero')
            ? Carbon::now()->diffInSeconds(
                $lastSignTime <= Carbon::today() ? $lastSignTime : Carbon::tomorrow(),
                false
            )
            : Carbon::now()->diffInSeconds(
                $lastSignTime->addHours((int) option('sign_gap_time')),
                false
            );

        if ($remainingTime <= 0) {
            [$min, $max] = explode(',', option('sign_score'));
            $acquiredScore = rand((int) $min, (int) $max);
            $acquiredScore = $filter->apply('sign_score', $acquiredScore);

            $dispatcher->dispatch('user.sign.before', [$acquiredScore]);

            $user->score += $acquiredScore;
            $user->last_sign_at = Carbon::now();
            $user->save();

            $dispatcher->dispatch('user.sign.after', [$acquiredScore]);

            return json(trans('user.sign-success', ['score' => $acquiredScore]), 0, [
                'score' => $user->score,
            ]);
        } else {
            return json('', 1);
        }
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

        $url = URL::signedRoute('auth.verify', ['user' => $user], null, false);

        try {
            Mail::to($user->email)->send(new EmailVerification(url($url)));
        } catch (\Exception $e) {
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
        /** @var User */
        $user = Auth::user();
        $addition = $request->except('action');

        $can = $filter->apply('user_can_edit_profile', true, [$action, $addition]);
        if ($can instanceof Rejection) {
            return json($can->getReason(), 1);
        }

        $dispatcher->dispatch('user.profile.updating', [$user, $action, $addition]);

        switch ($action) {
            case 'nickname':
                $request->validate(['new_nickname' => 'required']);

                $nickname = $request->input('new_nickname');
                $user->nickname = $nickname;
                $user->save();

                $dispatcher->dispatch('user.profile.updated', [$user, $action, $addition]);
                event(new UserProfileUpdated($action, $user));

                return json(trans('user.profile.nickname.success', ['nickname' => $nickname]), 0);

            case 'password':
                $request->validate([
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
                $data = $request->validate([
                    'email' => 'required|email',
                    'password' => 'required|min:6|max:32',
                ]);

                if (User::where('email', $data['email'])->count() > 0) {
                    return json(trans('user.profile.email.existed'), 1);
                }

                if (!$user->verifyPassword($data['password'])) {
                    return json(trans('user.profile.email.wrong-password'), 1);
                }

                $user->email = $data['email'];
                $user->verified = false;
                $user->save();

                $dispatcher->dispatch('user.profile.updated', [$user, $action, $addition]);
                event(new UserProfileUpdated($action, $user));

                Auth::logout();

                return json(trans('user.profile.email.success'), 0);

            case 'delete':
                $request->validate([
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
        $request->validate(['tid' => 'required|integer']);
        $tid = $request->input('tid');
        /** @var User */
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

            if (
                !$texture->public &&
                $user->uid !== $texture->uploader &&
                !$user->isAdmin()
            ) {
                return json(trans('skinlib.show.private'), 1);
            }

            $user->avatar = $tid;
            $user->save();

            $dispatcher->dispatch('user.avatar.updated', [$user, $tid]);

            return json(trans('user.profile.avatar.success'), 0);
        } else {
            return json(trans('skinlib.non-existent'), 1);
        }
    }

    public function toggleDarkMode()
    {
        /** @var User */
        $user = auth()->user();
        $user->is_dark_mode = !$user->is_dark_mode;
        $user->save();

        return response()->noContent();
    }
}

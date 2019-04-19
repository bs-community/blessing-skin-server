<?php

namespace App\Http\Controllers;

use App;
use URL;
use Mail;
use View;
use Session;
use App\Models\User;
use App\Models\Texture;
use Illuminate\Http\Request;
use App\Mail\EmailVerification;
use App\Events\UserProfileUpdated;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (! Auth::user()->verified) {
                $this->sendVerificationEmail();
            }

            return $next($request);
        })->only(['index', 'profile']);
    }

    public function index()
    {
        $user = Auth::user();

        return view('user.index')->with([
            'statistics' => [
                'players' => $this->calculatePercentageUsed($user->players->count(), option('score_per_player')),
                'storage' => $this->calculatePercentageUsed($user->getStorageUsed(), option('score_per_storage')),
            ],
            'announcement' => app('parsedown')->text(option_localized('announcement')),
            'extra' => ['unverified' => option('require_verification') && ! $user->verified],
        ]);
    }

    public function scoreInfo()
    {
        $user = Auth::user();

        return [
            'user' => [
                'score' => $user->score,
                'lastSignAt' => $user->last_sign_at,
            ],
            'stats' => [
                'players' => $this->calculatePercentageUsed($user->players->count(), option('score_per_player')),
                'storage' => $this->calculatePercentageUsed($user->getStorageUsed(), option('score_per_storage')),
            ],
            'signAfterZero' => option('sign_after_zero'),
            'signGapTime' => option('sign_gap_time'),
        ];
    }

    /**
     * Calculate percentage of resources used by user.
     *
     * @param  int $used
     * @param  int $rate
     * @return array
     */
    protected function calculatePercentageUsed($used, $rate)
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

    /**
     * Handle user signing.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sign()
    {
        $user = Auth::user();
        if ($user->canSign()) {
            $acquiredScore = $user->sign();
            $gap = option('sign_gap_time');

            return json([
                'errno'          => 0,
                'msg'            => trans('user.sign-success', ['score' => $acquiredScore]),
                'score'          => $user->score,
                'storage'        => $this->calculatePercentageUsed($user->getStorageUsed(), option('score_per_storage')),
                'remaining_time' => $gap > 1 ? round($gap) : $gap,
            ]);
        } else {
            $remaining_time = $this->getUserSignRemainingTimeWithPrecision();

            return json(trans('user.cant-sign-until', [
                'time' => $remaining_time >= 1
                    ? $remaining_time : round($remaining_time * 60),
                'unit' => $remaining_time >= 1
                    ? trans('user.time-unit-hour') : trans('user.time-unit-min'),
            ]), 1);
        }
    }

    public function getUserSignRemainingTimeWithPrecision($user = null)
    {
        $hours = ($user ?? Auth::user())->getSignRemainingTime() / 3600;

        return $hours > 1 ? round($hours) : $hours;
    }

    public function sendVerificationEmail()
    {
        if (! option('require_verification')) {
            return json(trans('user.verification.disabled'), 1);
        }

        // Rate limit of 60s
        $remain = 60 + session('last_mail_time', 0) - time();

        if ($remain > 0) {
            return json(trans('user.verification.frequent-mail'));
        }

        $user = Auth::user();

        if ($user->verified) {
            return json(trans('user.verification.verified'), 1);
        }

        $url = URL::signedRoute('auth.verify', ['uid' => $user->uid]);

        try {
            Mail::to($user->email)->send(new EmailVerification($url));
        } catch (\Exception $e) {
            // Write the exception to log
            report($e);

            return json(trans('user.verification.failed', ['msg' => $e->getMessage()]), 2);
        }

        Session::put('last_mail_time', time());

        return json(trans('user.verification.success'), 0);
    }

    public function profile()
    {
        $user = Auth::user();

        return view('user.profile')
            ->with('extra', [
                'unverified' => option('require_verification') && ! $user->verified,
                'admin' => $user->isAdmin(),
            ]);
    }

    public function handleProfile(Request $request)
    {
        $action = $request->input('action', '');
        $user = Auth::user();

        switch ($action) {
            case 'nickname':
                if (option('single_player', false)) {
                    return json(trans('user.profile.nickname.single'), 1);
                }

                $this->validate($request, [
                    'new_nickname' => 'required|no_special_chars|max:255',
                ]);

                $nickname = $request->input('new_nickname');
                $user->nickname = $nickname;
                $user->save();
                event(new UserProfileUpdated($action, $user));

                return json(trans('user.profile.nickname.success', ['nickname' => $nickname]), 0);

            case 'password':
                $this->validate($request, [
                    'current_password' => 'required|min:6|max:32',
                    'new_password'     => 'required|min:8|max:32',
                ]);

                if (! $user->verifyPassword($request->input('current_password'))) {
                    return json(trans('user.profile.password.wrong-password'), 1);
                }

                if ($user->changePassword($request->input('new_password'))) {
                    event(new UserProfileUpdated($action, $user));

                    Auth::logout();

                    return json(trans('user.profile.password.success'), 0);
                }

                break;   // @codeCoverageIgnore

            case 'email':
                $this->validate($request, [
                    'new_email' => 'required|email',
                    'password'  => 'required|min:6|max:32',
                ]);

                if (User::where('email', $request->new_email)->count() > 0) {
                    return json(trans('user.profile.email.existed'), 1);
                }

                if (! $user->verifyPassword($request->input('password'))) {
                    return json(trans('user.profile.email.wrong-password'), 1);
                }

                $user->email = $request->input('new_email');
                $user->verified = false;
                $user->save();

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

                if (! $user->verifyPassword($request->input('password'))) {
                    return json(trans('user.profile.delete.wrong-password'), 1);
                }

                Auth::logout();

                if ($user->delete()) {
                    session()->flush();

                    return response()
                        ->json([
                            'errno' => 0,
                            'msg' => trans('user.profile.delete.success'),
                        ]);
                }

                break;   // @codeCoverageIgnore

            default:
                return json(trans('general.illegal-parameters'), 1);
                break;
        }
    }

    // @codeCoverageIgnore

    /**
     * Set user avatar.
     *
     * @param Request $request
     */
    public function setAvatar(Request $request)
    {
        $this->validate($request, [
            'tid' => 'required|integer',
        ]);
        $tid = $request->input('tid');
        $user = auth()->user();

        if ($tid == 0) {
            $user->avatar = 0;
            $user->save();

            return json(trans('user.profile.avatar.success'), 0);
        }

        $result = Texture::find($tid);
        if ($result) {
            if ($result->type == 'cape') {
                return json(trans('user.profile.avatar.wrong-type'), 1);
            }

            $user->avatar = $tid;
            $user->save();

            return json(trans('user.profile.avatar.success'), 0);
        } else {
            return json(trans('skinlib.non-existent'), 1);
        }
    }

    // @codeCoverageIgnore
}

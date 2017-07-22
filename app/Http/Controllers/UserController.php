<?php

namespace App\Http\Controllers;

use App;
use View;
use Utils;
use App\Models\User;
use App\Models\Texture;
use Illuminate\Http\Request;
use App\Events\UserProfileUpdated;
use App\Exceptions\PrettyPageException;
use App\Services\Repositories\UserRepository;

class UserController extends Controller
{
    /**
     * Current user instance.
     *
     * @var App\Models\User
     */
    private $user = null;

    public function __construct(UserRepository $users)
    {
        $this->user = $users->get(session('uid'));
    }

    public function index()
    {
        return view('user.index')->with([
            'user' => $this->user,
            'statistics' => [
                'players' => $this->calculatePercentageUsed($this->user->players->count(), option('score_per_player')),
                'storage' => $this->calculatePercentageUsed($this->user->getStorageUsed(), option('score_per_storage'))
            ]
        ]);
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
        // init default value to avoid division by zero
        $result['used']       = $used;
        $result['total']      = 'UNLIMITED';
        $result['percentage'] = 0;

        if ($rate != 0) {
            $result['total'] = $used + floor($this->user->getScore() / $rate);
            $result['percentage'] = $result['total'] ? $used / $result['total'] * 100 : 100;
        }

        return $result;
    }

    /**
     * Handle user signing.
     *
     * @return void
     */
    public function sign()
    {
        if ($this->user->canSign()) {
            $acuiredScore = $this->user->sign();

            return json([
                'errno'          => 0,
                'msg'            => trans('user.sign-success', ['score' => $acuiredScore]),
                'score'          => $this->user->getScore(),
                'storage'        => $this->calculatePercentageUsed($this->user->getStorageUsed(), option('score_per_storage')),
                'remaining_time' => round($this->user->getSignRemainingTime() / 3600)
            ]);
        } else {
            return json(trans('user.cant-sign-until', ['time' => round($this->user->getSignRemainingTime() / 3600)]), 1);
        }
    }

    public function profile()
    {
        return view('user.profile')->with('user', $this->user);
    }

    /**
     * Handle changing user profile.
     *
     * @param  Request $request
     * @return mixed
     */
    public function handleProfile(Request $request, UserRepository $users)
    {
        $action = $request->input('action', '');

        switch ($action) {
            case 'nickname':
                $this->validate($request, [
                    'new_nickname' => 'required|nickname|max:255'
                ]);

                $nickname = $request->input('new_nickname');

                if ($this->user->setNickName($nickname))
                    return json(trans('user.profile.nickname.success', ['nickname' => $nickname]), 0);

                break;

            case 'password':
                $this->validate($request, [
                    'current_password' => 'required|min:6|max:16',
                    'new_password'     => 'required|min:8|max:16'
                ]);

                if (!$this->user->verifyPassword($request->input('current_password')))
                    return json(trans('user.profile.password.wrong-password'), 1);

                if ($this->user->changePasswd($request->input('new_password')))
                    return json(trans('user.profile.password.success'), 0);

                break;

            case 'email':
                $this->validate($request, [
                    'new_email' => 'required|email',
                    'password'  => 'required|min:6|max:16'
                ]);

                if ($users->get($request->input('new_email'), 'email')) {
                    return json(trans('user.profile.email.existed'), 1);
                }

                if (!$this->user->verifyPassword($request->input('password')))
                    return json(trans('user.profile.email.wrong-password'), 1);

                if ($this->user->setEmail($request->input('new_email')))
                    return json(trans('user.profile.email.success'), 0);

                break;

            case 'delete':
                $this->validate($request, [
                    'password' => 'required|min:6|max:16'
                ]);

                if (!$this->user->verifyPassword($request->input('password')))
                    return json(trans('user.profile.delete.wrong-password'), 1);

                if ($this->user->delete()) {
                    setcookie('uid',   '', time() - 3600, '/');
                    setcookie('token', '', time() - 3600, '/');

                    session()->flush();

                    return json(trans('user.profile.delete.success'), 0);
                }

                break;

            default:
                return json(trans('general.illegal-parameters'), 1);
                break;
        }

        event(new UserProfileUpdated($action, $this->user));

    }

    /**
     * Set user avatar.
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
                return json(trans('user.profile.avatar.wrong-type'), 1);

            if ($this->user->setAvatar($request->input('tid'))) {
                return json(trans('user.profile.avatar.success'), 0);
            }
        } else {
            return json(trans('user.profile.avatar.non-existent'), 1);
        }
    }

}

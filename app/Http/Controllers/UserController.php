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
    private $action = "";
    private $user   = null;

    public function __construct(Request $request, UserRepository $users)
    {
        $this->action = $request->input('action', '');
        $this->user   = $users->get(session('uid'));
    }

    public function index()
    {
        return view('user.index')->with('user', $this->user);
    }

    /**
     * Handle User Checking In
     *
     * @return void
     */
    public function checkIn()
    {
        if ($aquired_score = $this->user->checkIn()) {
            return json([
                'errno'          => 0,
                'msg'            => trans('user.checkin-success', ['score' => $aquired_score]),
                'score'          => $this->user->getScore(),
                'remaining_time' => $this->user->canCheckIn(true)
            ]);
        } else {
            return json(trans('user.cant-checkin-until', ['time' => $this->user->canCheckIn(true)]), 1);
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
                    return json(trans('user.profile.nickname.success', ['nickname' => $nickname]), 0);

                break;

            case 'password':
                $this->validate($request, [
                    'current_password' => 'required|min:8|max:16',
                    'new_password'     => 'required|min:8|max:16'
                ]);

                if (!$this->user->checkPasswd($request->input('current_password')))
                    return json(trans('user.profile.password.wrong-password'), 1);

                if ($this->user->changePasswd($request->input('new_password')))
                    return json(trans('user.profile.password.success'), 0);

                break;

            case 'email':
                $this->validate($request, [
                    'new_email' => 'required|email',
                    'password'  => 'required|min:8|max:16'
                ]);

                if (!$this->user->checkPasswd($request->input('password')))
                    return json(trans('user.profile.email.wrong-password'), 1);

                if ($this->user->setEmail($request->input('new_email')))
                    return json(trans('user.profile.email.success'), 0);

                break;

            case 'delete':
                $this->validate($request, [
                    'password' => 'required|min:8|max:16'
                ]);

                if (!$this->user->checkPasswd($request->input('password')))
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

        event(new UserProfileUpdated($this->action, $this->user));

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
                return json(trans('user.profile.avatar.wrong-type'), 1);

            if ($this->user->setAvatar($request->input('tid'))) {
                return json(trans('user.profile.avatar.success'), 0);
            }
        } else {
            return json(trans('user.profile.avatar.non-existent'), 1);
        }
    }

}

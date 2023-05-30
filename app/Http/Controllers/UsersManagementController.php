<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UsersManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(function (Request $request, $next) {
            /** @var User */
            $targetUser = $request->route('user');
            /** @var User */
            $authUser = $request->user();

            if (
                $targetUser->isNot($authUser)
                && $targetUser->permission >= $authUser->permission
            ) {
                return json(trans('admin.users.operations.no-permission'), 1)
                    ->setStatusCode(403);
            }

            return $next($request);
        })->except(['list']);
    }

    public function list(Request $request)
    {
        $q = $request->input('q');

        return User::usingSearchString($q)->paginate(10);
    }

    public function email(User $user, Request $request, Dispatcher $dispatcher)
    {
        $data = $request->validate([
            'email' => [
                'required', 'email', Rule::unique('users')->ignore($user),
            ],
        ]);
        $email = $data['email'];

        $dispatcher->dispatch('user.email.updating', [$user, $email]);

        $old = $user->replicate();
        $user->email = $email;
        $user->save();

        $dispatcher->dispatch('user.email.updated', [$user, $old]);

        return json(trans('admin.users.operations.email.success'), 0);
    }

    public function verification(User $user, Dispatcher $dispatcher)
    {
        $dispatcher->dispatch('user.verification.updating', [$user]);

        $user->verified = !$user->verified;
        $user->save();

        $dispatcher->dispatch('user.verification.updated', [$user]);

        return json(trans('admin.users.operations.verification.success'), 0);
    }

    public function nickname(User $user, Request $request, Dispatcher $dispatcher)
    {
        $data = $request->validate([
            'nickname' => 'required|string',
        ]);
        $nickname = $data['nickname'];

        $dispatcher->dispatch('user.nickname.updating', [$user, $nickname]);

        $old = $user->replicate();
        $user->nickname = $nickname;
        $user->save();

        $dispatcher->dispatch('user.nickname.updated', [$user, $old]);

        return json(trans('admin.users.operations.nickname.success', [
            'new' => $request->input('nickname'),
        ]), 0);
    }

    public function password(User $user, Request $request, Dispatcher $dispatcher)
    {
        $data = $request->validate([
            'password' => 'required|string|min:8|max:16',
        ]);
        $password = $data['password'];

        $dispatcher->dispatch('user.password.updating', [$user, $password]);

        $user->changePassword($password);
        $user->save();

        $dispatcher->dispatch('user.password.updated', [$user]);

        return json(trans('admin.users.operations.password.success'), 0);
    }

    public function score(User $user, Request $request, Dispatcher $dispatcher)
    {
        $data = $request->validate([
            'score' => 'required|integer',
        ]);
        $score = (int) $data['score'];

        $dispatcher->dispatch('user.score.updating', [$user, $score]);

        $old = $user->replicate();
        $user->score = $score;
        $user->save();

        $dispatcher->dispatch('user.score.updated', [$user, $old]);

        return json(trans('admin.users.operations.score.success'), 0);
    }

    public function permission(User $user, Request $request, Dispatcher $dispatcher)
    {
        $data = $request->validate([
            'permission' => [
                'required',
                Rule::in([User::BANNED, User::NORMAL, User::ADMIN]),
            ],
        ]);
        $permission = (int) $data['permission'];

        if (
            $permission === User::ADMIN
            && $request->user()->permission < User::SUPER_ADMIN
        ) {
            return json(trans('admin.users.operations.no-permission'), 1)
                ->setStatusCode(403);
        }

        if ($user->is($request->user())) {
            return json(trans('admin.users.operations.no-permission'), 1)
                ->setStatusCode(403);
        }

        $dispatcher->dispatch('user.permission.updating', [$user, $permission]);

        $old = $user->replicate();
        $user->permission = $permission;
        $user->save();

        if ($permission === User::BANNED) {
            $dispatcher->dispatch('user.banned', [$user]);
        }

        $dispatcher->dispatch('user.permission.updated', [$user, $old]);

        return json(trans('admin.users.operations.permission'), 0);
    }

    public function delete(User $user, Dispatcher $dispatcher)
    {
        $dispatcher->dispatch('user.deleting', [$user]);

        $user->delete();

        $dispatcher->dispatch('user.deleted', [$user]);

        return json(trans('admin.users.operations.delete.success'), 0);
    }
}

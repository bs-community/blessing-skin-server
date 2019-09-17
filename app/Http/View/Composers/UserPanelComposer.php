<?php

namespace App\Http\View\Composers;

use App\Models\User;
use Illuminate\View\View;
use Illuminate\Contracts\Events\Dispatcher;

class UserPanelComposer
{
    /** @var Dispatcher */
    protected $dispatcher;

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function compose(View $view)
    {
        $user = auth()->user();
        $roles = [
            User::BANNED => 'banned',
            User::NORMAL => 'normal',
            User::ADMIN  => 'admin',
            User::SUPER_ADMIN => 'super-admin',
        ];
        $role = $roles[$user->permission];
        $avatar = url('avatar/45/'.base64_encode($user->email).'.png?tid='.$user->avatar);

        $badges = [];
        $this->dispatcher->dispatch(new \App\Events\RenderingBadges($badges));

        $view->with([
            'user' => $user,
            'role' => trans("admin.users.status.$role"),
            'avatar' => $avatar,
            'badges' => $badges,
        ]);
    }
}

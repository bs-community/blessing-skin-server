<?php

namespace App\Http\View\Composers;

use Blessing\Filter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserMenuComposer
{
    protected Request $request;

    protected Filter $filter;

    public function __construct(Request $request, Filter $filter)
    {
        $this->request = $request;
        $this->filter = $filter;
    }

    public function compose(View $view)
    {
        $user = auth()->user();
        $avatarUrl = route('avatar.texture', ['tid' => $user->avatar, 'size' => 36], false);
        $avatar = $this->filter->apply('user_avatar', $avatarUrl, [$user]);
        $cli = $this->request->is('admin', 'admin/*');

        $view->with(compact('user', 'avatar', 'cli'));
    }
}

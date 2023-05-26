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
        $avatarPNG = route(
            'avatar.texture',
            ['tid' => $user->avatar, 'size' => 36, 'png' => true],
            false
        );
        $avatarPNG = $this->filter->apply('user_avatar', $avatarPNG, [$user]);

        $menuItems = [
            ['label' => trans('general.user-center'), 'link' => route('user.home')],
            ['label' => trans('general.profile'), 'link' => route('user.profile.view')],
        ];
        if ($user->isAdmin()) {
            array_push(
                $menuItems,
                ['label' => '', 'link' => '#divider'],
                ['label' => trans('general.admin-panel'), 'link' => route('admin.view')],
                ['label' => trans('general.user-manage'), 'link' => route('admin.users.view')],
                ['label' => trans('general.report-manage'), 'link' => route('admin.reports.view')],
                ['label' => 'Web CLI', 'link' => '#launch-cli'],
            );
        }
        $menuItems = $this->filter->apply('user_menu', $menuItems, [$user]);

        $view->with([
            'user' => $user,
            'avatar' => $avatar,
            'avatar_png' => $avatarPNG,
            'menu' => $menuItems,
        ]);
    }
}

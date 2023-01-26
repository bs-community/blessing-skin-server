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
            ['label' => trans('general.profile'), 'link' => route('user.profile.')],
        ];
        if ($user->isAdmin()) {
            array_push(
                $menuItems,
                ['label' => '', 'link' => '#divider'],
                ['label' => trans('general.admin-panel'), 'link' => route('admin.')],
                ['label' => trans('general.user-manage'), 'link' => route('admin.users.')],
                ['label' => trans('general.report-manage'), 'link' => route('admin.reports.')],
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

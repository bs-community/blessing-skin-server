<?php

namespace App\Http\View\Composers;

use App\Models\User;
use Blessing\Filter;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\View\View;

class UserPanelComposer
{
    protected Dispatcher $dispatcher;

    protected Filter $filter;

    public function __construct(Dispatcher $dispatcher, Filter $filter)
    {
        $this->dispatcher = $dispatcher;
        $this->filter = $filter;
    }

    public function compose(View $view)
    {
        /** @var User */
        $user = auth()->user();
        $avatarUrl = route('avatar.texture', ['tid' => $user->avatar, 'size' => 45], false);
        $avatar = $this->filter->apply('user_avatar', $avatarUrl, [$user]);

        $badges = [];
        if ($user->isAdmin()) {
            $badges[] = ['text' => 'STAFF', 'color' => 'primary'];
        }
        $this->dispatcher->dispatch(new \App\Events\RenderingBadges($badges));
        $badges = $this->filter->apply('user_badges', $badges, [$user]);

        $view->with(compact('user', 'avatar', 'badges'));
    }
}

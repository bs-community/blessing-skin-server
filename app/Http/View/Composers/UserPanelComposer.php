<?php

namespace App\Http\View\Composers;

use Blessing\Filter;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\View\View;

class UserPanelComposer
{
    /** @var Dispatcher */
    protected $dispatcher;

    /** @var Filter */
    protected $filter;

    public function __construct(Dispatcher $dispatcher, Filter $filter)
    {
        $this->dispatcher = $dispatcher;
        $this->filter = $filter;
    }

    public function compose(View $view)
    {
        $user = auth()->user();
        $avatarUrl = url('/avatar/user/'.$user->uid.'?size=45');
        $avatar = $this->filter->apply('user_avatar', $avatarUrl, [$user]);

        $badges = [];
        if (auth()->user()->isAdmin()) {
            $badges[] = ['text' => 'STAFF', 'color' => 'primary'];
        }
        $this->dispatcher->dispatch(new \App\Events\RenderingBadges($badges));

        $view->with(compact('user', 'avatar', 'badges'));
    }
}

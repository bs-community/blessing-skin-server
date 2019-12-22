<?php

namespace App\Http\View\Composers;

use App\Services\Filter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserMenuComposer
{
    /** @var Request */
    protected $request;

    /** @var Filter */
    protected $filter;

    public function __construct(Request $request, Filter $filter)
    {
        $this->request = $request;
        $this->filter = $filter;
    }

    public function compose(View $view)
    {
        $user = auth()->user();
        $email = base64_encode($user->email);
        $avatar = $this->filter->apply(
            'user_avatar',
            url('/avatar/user/'.$user->uid.'/25'),
            [$user]
        );

        $view->with(compact('user', 'avatar'));
    }
}

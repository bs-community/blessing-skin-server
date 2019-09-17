<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Http\Request;

class UserMenuComposer
{
    /** @var Request */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function compose(View $view)
    {
        $user = auth()->user();
        $view->with('user', $user);

        if ($this->request->is('skinlib*') || $this->request->is('/')) {
            $view->with(
                'tiny_avatar',
                url('avatar/25/'.base64_encode($user->email).'.png?tid='.$user->avatar)
            );
        }

        $view->with(
            'avatar',
            url('avatar/128/'.base64_encode($user->email).'.png?tid='.$user->avatar)
        );
    }
}

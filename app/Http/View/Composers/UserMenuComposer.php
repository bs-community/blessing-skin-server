<?php

namespace App\Http\View\Composers;

use Illuminate\Http\Request;
use Illuminate\View\View;

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
        $email = base64_encode($user->email);
        $avatar = $user->avatar;

        $view->with([
            'user' => $user,
            'avatar' => url('avatar/25/'.$email.'.png?tid='.$avatar),
        ]);
    }
}

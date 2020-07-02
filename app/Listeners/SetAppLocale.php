<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Http\Request;

class SetAppLocale
{
    /** @var Request */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function handle($event)
    {
        /** @var User */
        $user = $event->user;

        if ($this->request->has('lang')) {
            $user->locale = $this->request->input('lang');
            $user->save();

            return;
        }

        $locale = $user->locale;
        if ($locale) {
            app()->setLocale($locale);
        }
    }
}

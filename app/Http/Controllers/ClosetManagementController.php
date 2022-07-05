<?php

namespace App\Http\Controllers;

use App\Models\Texture;
use App\Models\User;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;

class ClosetManagementController extends Controller
{
    public function list(User $user)
    {
        return $user->closet;
    }

    public function add(Request $request, Dispatcher $dispatcher, User $user)
    {
        $tid = $request->input('tid');
        $texture = Texture::find($tid);
        if (!$texture) {
            return json(trans('user.closet.add.not-found'), 1);
        }

        if ($user->closet()->where('tid', $request->tid)->count() > 0) {
            return json(trans('user.closet.add.repeated'), 1);
        }

        $name = $texture->name;

        $dispatcher->dispatch('closet.adding', [$tid, $name, $user]);

        $user->closet()->attach($texture->tid, ['item_name' => $name]);

        $dispatcher->dispatch('closet.added', [$texture, $name, $user]);

        return json('', 0, compact('user', 'texture'));
    }

    public function remove(Request $request, Dispatcher $dispatcher, User $user)
    {
        $tid = $request->input('tid');
        $dispatcher->dispatch('closet.removing', [$tid, $user]);

        $item = $user->closet()->find($tid);
        if (empty($item)) {
            return json(trans('user.closet.remove.non-existent'), 1);
        }

        $user->closet()->detach($tid);

        $texture = Texture::find($tid);

        $dispatcher->dispatch('closet.removed', [$texture, $user]);

        return json('', 0, compact('user', 'texture'));
    }
}

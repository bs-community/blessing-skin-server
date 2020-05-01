<?php

namespace App\Http\Controllers;

use App\Models\Texture;
use App\Models\User;
use Illuminate\Http\Request;

class ClosetManagementController extends Controller
{
    public function list($uid)
    {
        /** @var User */
        $user = User::findOrFail($uid);

        return $user->closet;
    }

    public function add(Request $request, $uid)
    {
        /** @var Texture */
        $texture = Texture::findOrFail($request->input('tid'));

        /** @var User */
        $user = User::findOrFail($uid);
        $user->closet()->attach($texture->tid, ['item_name' => $texture->name]);

        return json('', 0, compact('user', 'texture'));
    }

    public function remove(Request $request, $uid)
    {
        /** @var Texture */
        $texture = Texture::findOrFail($request->input('tid'));

        /** @var User */
        $user = User::findOrFail($uid);
        $user->closet()->detach($texture->tid);

        return json('', 0, compact('user', 'texture'));
    }
}

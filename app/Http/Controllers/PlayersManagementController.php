<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Texture;
use App\Models\User;
use App\Rules;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlayersManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(function (Request $request, $next) {
            /** @var Player */
            $player = $request->route('player');
            $owner = $player->user;

            /** @var User */
            $currentUser = $request->user();

            if (
                $owner->uid !== $currentUser->uid &&
                $owner->permission >= $currentUser->permission
            ) {
                return json(trans('admin.players.no-permission'), 1)
                    ->setStatusCode(403);
            }

            return $next($request);
        })->except(['list']);
    }

    public function list(Request $request)
    {
        $query = $request->query('q');

        return Player::usingSearchString($query)->paginate(10);
    }

    public function name(
        Player $player,
        Request $request,
        Dispatcher $dispatcher
    ) {
        $name = $request->validate([
            'player_name' => [
                'required',
                new Rules\PlayerName(),
                'min:'.option('player_name_length_min'),
                'max:'.option('player_name_length_max'),
                'unique:players,name',
            ],
        ])['player_name'];

        $dispatcher->dispatch('player.name.updating', [$player, $name]);

        $oldName = $player->name;
        $player->name = $name;
        $player->save();

        $dispatcher->dispatch('player.name.updated', [$player, $oldName]);

        return json(trans('admin.players.name.success', ['player' => $player->name]), 0);
    }

    public function owner(
        Player $player,
        Request $request,
        Dispatcher $dispatcher
    ) {
        $uid = $request->validate(['uid' => 'required|integer'])['uid'];

        $dispatcher->dispatch('player.owner.updating', [$player, $uid]);

        /** @var User */
        $user = User::find($request->uid);
        if (empty($user)) {
            return json(trans('admin.users.operations.non-existent'), 1);
        }

        $player->uid = $uid;
        $player->save();

        $dispatcher->dispatch('player.owner.updated', [$player, $user]);

        return json(trans('admin.players.owner.success', [
            'player' => $player->name,
            'user' => $user->nickname,
        ]), 0);
    }

    public function texture(
        Player $player,
        Request $request,
        Dispatcher $dispatcher
    ) {
        $data = $request->validate([
            'tid' => 'required|integer',
            'type' => ['required', Rule::in(['skin', 'cape'])],
        ]);
        $tid = (int) $data['tid'];
        $type = $data['type'];

        $dispatcher->dispatch('player.texture.updating', [$player, $type, $tid]);

        if (Texture::where('tid', $tid)->doesntExist() && $tid !== 0) {
            return json(trans('admin.players.textures.non-existent', ['tid' => $tid]), 1);
        }

        $field = 'tid_'.$type;
        $previousTid = $player->$field;
        $player->$field = $tid;
        $player->save();

        $dispatcher->dispatch('player.texture.updated', [$player, $type, $previousTid]);

        return json(trans('admin.players.textures.success', ['player' => $player->name]), 0);
    }

    public function delete(
        Player $player,
        Dispatcher $dispatcher
    ) {
        $dispatcher->dispatch('player.deleting', [$player]);

        $player->delete();

        $dispatcher->dispatch('player.deleted', [$player]);

        return json(trans('admin.players.delete.success'), 0);
    }
}

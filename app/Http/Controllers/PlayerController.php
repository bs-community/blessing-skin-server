<?php

namespace App\Http\Controllers;

use View;
use Event;
use Utils;
use Option;
use App\Models\User;
use App\Models\Player;
use App\Models\Texture;
use Illuminate\Http\Request;
use App\Exceptions\PrettyPageException;

class PlayerController extends Controller
{
    /**
     * User Instance.
     *
     * @var \App\Models\User
     */
    private $user;

    /**
     * Player Instance.
     *
     * @var \App\Models\Player
     */
    private $player;

    public function __construct(Request $request)
    {
        $this->user = new User(session('uid'));

        if ($request->has('pid'))
            $this->player = new Player($request->pid);
    }

    public function index()
    {
        return view('user.player')->with('players', $this->user->getPlayers()->toArray())->with('user', $this->user);
    }

    public function add(Request $request)
    {
        $this->validate($request, [
            'player_name' => 'required|'.(Option::get('allow_chinese_playername') == "1") ? 'pname_chinese' : 'player_name'
        ]);

        $player = new Player(null, $request->input('player_name'));

        if ($player->is_registered) {
            return json(trans('user.player.add.repeated'), 6);
        }

        if ($this->user->getScore() < Option::get('score_per_player')) {
            return json(trans('user.player.add.lack-score'), 7);
        }

        $player->register($this->user);

        return json(trans('user.player.add.success', ['name' => $request->input('player_name')]), 0);
    }

    public function delete(Request $request)
    {
        $player_name = $this->player->player_name;

        if ($this->player->delete()) {
            $this->user->setScore(Option::get('score_per_player'), 'plus');

            return json(trans('user.player.delete.success', ['name' => $player_name]), 0);
        }
    }

    public function show()
    {
        return json_encode($this->player->model->toArray(), JSON_NUMERIC_CHECK);
    }

    public function rename(Request $request)
    {
        $this->validate($request, [
            'new_player_name' => 'required|'.(Option::get('allow_chinese_playername') == "1") ? 'pname_chinese' : 'player_name'
        ]);

        $new_name = $request->input('new_player_name');

        if ((new Player(null, $new_name))->is_registered) {
            return json(trans('user.player.rename.repeated'), 6);
        }

        $old_name = $this->player->player_name;

        $this->player->rename($new_name);

        return json(trans('user.player.rename.success', ['old' => $old_name, 'new' => $new_name]), 0);
    }

    /**
     * A wrapper of Player::setTexture()
     */
    public function setTexture(Request $request)
    {
        $this->validate($request, [
            'tid' => 'required|integer'
        ]);

        if (!($texture = Texture::find($request->tid)))
            return json(trans('skinlib.un-existent'), 6);

        $field_name = "tid_{$texture->type}";

        $this->player->setTexture([$field_name => $request->tid]);

        return json(trans('user.player.set.success', ['name' => $this->player->player_name]), 0);
    }

    public function clearTexture()
    {
        $this->player->clearTexture();

        return json(trans('user.player.clear.success', ['name' => $this->player->player_name]), 0);
    }

    public function setPreference(Request $request)
    {
        $this->validate($request, [
            'preference' => 'required|preference'
        ]);

        $this->player->setPreference($request->preference);

        return json(trans('user.player.preference.success', ['name' => $this->player->player_name, 'preference' => $request->preference]), 0);
    }

}

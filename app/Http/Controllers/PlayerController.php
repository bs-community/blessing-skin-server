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
use App\Events\PlayerWasAdded;
use App\Events\PlayerWasDeleted;
use App\Events\CheckPlayerExists;
use App\Events\PlayerWillBeAdded;
use App\Events\PlayerWillBeDeleted;
use App\Exceptions\PrettyPageException;
use App\Services\Repositories\UserRepository;

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

    public function __construct(Request $request, UserRepository $users)
    {
        $this->user = $users->get(session('uid'));

        if ($request->has('pid'))
            $this->player = Player::find($request->pid);
    }

    public function index()
    {
        return view('user.player')->with('players', $this->user->players->toArray())->with('user', $this->user);
    }

    public function add(Request $request)
    {
        $this->validate($request, [
            'player_name' => 'required|'.(option('allow_chinese_playername') ? 'pname_chinese' : 'playername')
        ]);

        Event::fire(new CheckPlayerExists($request->input('player_name')));

        if (!Player::where('player_name', $request->input('player_name'))->get()->isEmpty()) {
            return json(trans('user.player.add.repeated'), 6);
        }

        if ($this->user->getScore() < Option::get('score_per_player')) {
            return json(trans('user.player.add.lack-score'), 7);
        }

        Event::fire(new PlayerWillBeAdded($request->input('player_name')));

        $player = new Player;

        $player->uid           = $this->user->uid;
        $player->player_name   = $request->input('player_name');
        $player->preference    = "default";
        $player->last_modified = Utils::getTimeFormatted();
        $player->save();

        Event::fire(new PlayerWasAdded($player));

        $this->user->setScore(option('score_per_player'), 'minus');

        return json(trans('user.player.add.success', ['name' => $request->input('player_name')]), 0);
    }

    public function delete(Request $request)
    {
        $player_name = $this->player->player_name;

        Event::fire(new PlayerWillBeDeleted($this->player));

        if ($this->player->delete()) {
            $this->user->setScore(Option::get('score_per_player'), 'plus');

            Event::fire(new PlayerWasDeleted($player_name));

            return json(trans('user.player.delete.success', ['name' => $player_name]), 0);
        }
    }

    public function show()
    {
        return json_encode($this->player->toArray(), JSON_NUMERIC_CHECK);
    }

    public function rename(Request $request)
    {
        $this->validate($request, [
            'new_player_name' => 'required|'.(option('allow_chinese_playername') ? 'pname_chinese' : 'playername')
        ]);

        $new_name = $request->input('new_player_name');

        if (!Player::where('player_name', $new_name)->get()->isEmpty()) {
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
        foreach ($request->input('tid') as $key => $value) {
            if (!($texture = Texture::find($value)))
                return json(trans('skinlib.un-existent'), 6);

            $field_name = "tid_{$texture->type}";

            $this->player->setTexture([$field_name => $value]);
        }

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

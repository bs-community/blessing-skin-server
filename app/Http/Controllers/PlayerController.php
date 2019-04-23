<?php

namespace App\Http\Controllers;

use View;
use Event;
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
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\CheckPlayerExist;
use App\Http\Middleware\CheckPlayerOwner;

class PlayerController extends Controller
{
    /**
     * Player Instance.
     *
     * @var \App\Models\Player
     */
    private $player;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if ($request->has('pid')) {
                if ($this->player = Player::find($request->pid)) {
                    foreach (['skin', 'cape'] as $type) {
                        $field = "tid_$type";
                        if (! Texture::find($this->player->$field)) {
                            $this->player->$field = 0;
                        }
                    }
                    $this->player->save();
                }
            }

            return $next($request);
        });

        $this->middleware([CheckPlayerExist::class, CheckPlayerOwner::class], [
            'only' => ['delete', 'rename', 'setTexture', 'clearTexture'],
        ]);
    }

    public function index()
    {
        return view('user.player')
            ->with('extra', [
                'rule' => trans('user.player.player-name-rule.'.option('player_name_rule')),
                'length' => trans(
                    'user.player.player-name-length',
                    ['min' => option('player_name_length_min'), 'max' => option('player_name_length_max')]
                ),
            ]);
    }

    public function listAll()
    {
        return json(
            '',
            0,
            Auth::user()
                ->players()
                ->select('pid', 'name', 'tid_skin', 'tid_cape')
                ->get()
                ->toArray()
        );
    }

    public function add(Request $request)
    {
        $user = Auth::user();

        if (option('single_player', false)) {
            return json(trans('user.player.add.single'), 1);
        }

        $name = $this->validate($request, [
            'name' => 'required|player_name|min:'.option('player_name_length_min').'|max:'.option('player_name_length_max'),
        ])['name'];

        event(new CheckPlayerExists($name));

        if (! Player::where('name', $name)->get()->isEmpty()) {
            return json(trans('user.player.add.repeated'), 6);
        }

        if ($user->score < Option::get('score_per_player')) {
            return json(trans('user.player.add.lack-score'), 7);
        }

        event(new PlayerWillBeAdded($name));

        $player = new Player;

        $player->uid = $user->uid;
        $player->name = $name;
        $player->tid_skin = 0;
        $player->save();

        event(new PlayerWasAdded($player));

        $user->setScore(option('score_per_player'), 'minus');

        return json(trans('user.player.add.success', ['name' => $name]), 0);
    }

    public function delete()
    {
        $playerName = $this->player->name;

        if (option('single_player', false)) {
            return json(trans('user.player.delete.single'), 1);
        }

        event(new PlayerWillBeDeleted($this->player));

        $this->player->delete();

        if (option('return_score')) {
            Auth::user()->setScore(Option::get('score_per_player'), 'plus');
        }

        event(new PlayerWasDeleted($playerName));

        return json(trans('user.player.delete.success', ['name' => $playerName]), 0);
    }

    public function rename(Request $request)
    {
        $this->validate($request, [
            'new_player_name' => 'required|player_name|min:'.option('player_name_length_min').'|max:'.option('player_name_length_max'),
        ]);

        $newName = $request->input('new_player_name');

        if (! Player::where('name', $newName)->get()->isEmpty()) {
            return json(trans('user.player.rename.repeated'), 6);
        }

        $oldName = $this->player->name;
        $this->player->name = $newName;
        $this->player->save();

        if (option('single_player', false)) {
            $user = auth()->user();
            $user->nickname = $newName;
            $user->save();
        }

        return json(trans('user.player.rename.success', ['old' => $oldName, 'new' => $newName]), 0);
    }

    public function setTexture(Request $request)
    {
        foreach ($request->input('tid') as $key => $value) {
            $texture = Texture::find($value);

            if (! $texture) {
                return json(trans('skinlib.un-existent'), 6);
            }

            $field = $texture->type == 'cape' ? 'tid_cape' : 'tid_skin';

            $this->player->$field = $value;
            $this->player->save();
        }

        return json(trans('user.player.set.success', ['name' => $this->player->name]), 0);
    }

    public function clearTexture(Request $request)
    {
        array_map(function ($type) use ($request) {
            if ($request->input($type)) {
                $field = "tid_$type";
                $this->player->$field = 0;
            }
        }, ['skin', 'cape']);
        $this->player->save();

        return json(trans('user.player.clear.success', ['name' => $this->player->name]), 0);
    }

    public function bind(Request $request)
    {
        $name = $this->validate($request, [
            'player' => 'required|player_name|min:'.option('player_name_length_min').'|max:'.option('player_name_length_max'),
        ])['player'];
        $user = Auth::user();

        event(new CheckPlayerExists($name));
        $player = Player::where('name', $name)->first();
        if (! $player) {
            event(new PlayerWillBeAdded($name));

            $player = new Player;
            $player->uid = $user->uid;
            $player->name = $name;
            $player->tid_skin = 0;
            $player->save();

            event(new PlayerWasAdded($player));
        } elseif ($player->uid != $user->uid) {
            return json(trans('user.player.rename.repeated'), 1);
        }

        $user->players()->where('name', '<>', $name)->delete();
        $user->nickname = $name;
        $user->save();

        return json(trans('user.player.bind.success'), 0);
    }
}

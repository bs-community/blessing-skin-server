<?php

namespace App\Http\Controllers;

use App\Events\PlayerWasAdded;
use App\Events\PlayerWasDeleted;
use App\Events\PlayerWillBeAdded;
use App\Events\PlayerWillBeDeleted;
use App\Http\Middleware\CheckPlayerExist;
use App\Http\Middleware\CheckPlayerOwner;
use App\Models\Player;
use App\Models\Texture;
use App\Rules;
use Auth;
use Blessing\Filter;
use Blessing\Rejection;
use Event;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use Option;
use View;

class PlayerController extends Controller
{
    public function __construct()
    {
        $this->middleware([CheckPlayerExist::class, CheckPlayerOwner::class], [
            'only' => ['delete', 'rename', 'setTexture', 'clearTexture'],
        ]);
    }

    public function index(Filter $filter)
    {
        $grid = [
            'layout' => [
                ['md-6', 'md-6'],
            ],
            'widgets' => [
                [
                    [
                        'user.widgets.players.list',
                        'user.widgets.players.notice',
                    ],
                    ['shared.previewer'],
                ],
            ],
        ];
        $grid = $filter->apply('grid:user.player', $grid);

        return view('user.player')
            ->with('grid', $grid)
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
        $players = Auth::user()
            ->players()
            ->select('pid', 'name', 'tid_skin', 'tid_cape')
            ->get()
            ->toArray();

        return json('', 0, $players);
    }

    public function add(Request $request, Dispatcher $dispatcher)
    {
        $user = Auth::user();

        if (option('single_player', false)) {
            return json(trans('user.player.add.single'), 1);
        }

        $name = $this->validate($request, [
            'name' => [
                'required',
                new Rules\PlayerName(),
                'min:'.option('player_name_length_min'),
                'max:'.option('player_name_length_max'),
            ],
        ])['name'];

        $dispatcher->dispatch('player.add.attempt', [$name, $user]);

        if (!Player::where('name', $name)->get()->isEmpty()) {
            return json(trans('user.player.add.repeated'), 6);
        }

        if ($user->score < Option::get('score_per_player')) {
            return json(trans('user.player.add.lack-score'), 7);
        }

        $dispatcher->dispatch('player.adding', [$name, $user]);
        event(new PlayerWillBeAdded($name));

        $player = new Player();
        $player->uid = $user->uid;
        $player->name = $name;
        $player->tid_skin = 0;
        $player->tid_cape = 0;
        $player->save();

        $user->score -= option('score_per_player');
        $user->save();

        $dispatcher->dispatch('player.added', [$player, $user]);
        event(new PlayerWasAdded($player));

        return json(trans('user.player.add.success', ['name' => $name]), 0, $player->toArray());
    }

    public function delete(Dispatcher $dispatcher, $pid)
    {
        $user = auth()->user();
        $player = Player::find($pid);
        $playerName = $player->name;

        $dispatcher->dispatch('player.delete.attempt', [$player, $user]);

        if (option('single_player', false)) {
            return json(trans('user.player.delete.single'), 1);
        }

        $dispatcher->dispatch('player.deleting', [$player, $user]);
        event(new PlayerWillBeDeleted($player));

        $player->delete();

        if (option('return_score')) {
            $user->score += option('score_per_player');
            $user->save();
        }

        $dispatcher->dispatch('player.deleted', [$player, $user]);
        event(new PlayerWasDeleted($playerName));

        return json(trans('user.player.delete.success', ['name' => $playerName]), 0);
    }

    public function rename(
        Request $request,
        Dispatcher $dispatcher,
        Filter $filter,
        $pid
    ) {
        $newName = $this->validate($request, [
            'name' => [
                'required',
                new Rules\PlayerName(),
                'min:'.option('player_name_length_min'),
                'max:'.option('player_name_length_max'),
            ],
        ])['name'];
        $player = Player::find($pid);

        $dispatcher->dispatch('player.renaming', [$player, $newName]);

        $can = $filter->apply('user_can_rename_player', true, [$player, $newName]);
        if ($can instanceof Rejection) {
            return json($can->getReason(), 1);
        }

        if (!Player::where('name', $newName)->get()->isEmpty()) {
            return json(trans('user.player.rename.repeated'), 6);
        }

        $oldName = $player->name;
        $player->name = $newName;
        $player->save();

        if (option('single_player', false)) {
            $user = auth()->user();
            $user->nickname = $newName;
            $user->save();
        }

        $dispatcher->dispatch('player.renamed', [$player, $oldName]);

        return json(trans('user.player.rename.success', ['old' => $oldName, 'new' => $newName]), 0, $player->toArray());
    }

    public function setTexture(Request $request, Dispatcher $dispatcher, $pid)
    {
        $player = Player::find($pid);
        foreach (['skin', 'cape'] as $type) {
            $tid = $request->input($type);
            if ($tid) {
                $texture = Texture::find($tid);
                if (!$texture) {
                    return json(trans('skinlib.non-existent'), 1);
                }

                $dispatcher->dispatch('player.texture.updating', [$player, $texture]);

                $field = "tid_$type";
                $player->$field = $tid;
                $player->save();

                $dispatcher->dispatch('player.texture.updated', [$player, $texture]);
            }
        }

        return json(trans('user.player.set.success', ['name' => $player->name]), 0, $player->toArray());
    }

    public function clearTexture(Request $request, Dispatcher $dispatcher, $pid)
    {
        $player = Player::find($pid);
        $types = $request->input('type', []);

        foreach (['skin', 'cape'] as $type) {
            if ($request->has($type) || in_array($type, $types)) {
                $dispatcher->dispatch('player.texture.resetting', [$player, $type]);

                $field = "tid_$type";
                $player->$field = 0;
                $player->save();

                $dispatcher->dispatch('player.texture.reset', [$player, $type]);
            }
        }

        return json(trans('user.player.clear.success', ['name' => $player->name]), 0, $player->toArray());
    }

    public function bind(Request $request, Dispatcher $dispatcher)
    {
        $name = $this->validate($request, [
            'player' => [
                'required',
                new Rules\PlayerName(),
                'min:'.option('player_name_length_min'),
                'max:'.option('player_name_length_max'),
            ],
        ])['player'];
        $user = Auth::user();

        $player = Player::where('name', $name)->first();
        if (!$player) {
            $dispatcher->dispatch('player.adding', [$name, $user]);
            event(new PlayerWillBeAdded($name));

            $player = new Player();
            $player->uid = $user->uid;
            $player->name = $name;
            $player->tid_skin = 0;
            $player->save();

            $dispatcher->dispatch('player.added', [$player, $user]);
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

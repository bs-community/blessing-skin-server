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
use App\Exceptions\PrettyPageException;
use App\Http\Middleware\CheckPlayerExist;
use App\Http\Middleware\CheckPlayerOwner;
use App\Services\Repositories\UserRepository;

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
                    $this->player->checkForInvalidTextures();
                }
            }

            return $next($request);
        });

        $this->middleware([CheckPlayerExist::class, CheckPlayerOwner::class], [
            'only' => ['delete', 'rename', 'setTexture', 'clearTexture']
        ]);
    }

    public function index()
    {
        $user = Auth::user();
        return view('user.player')
            ->with('players', $user->players->toArray())
            ->with('user', $user);
    }

    public function listAll()
    {
        return Auth::user()
            ->players()
            ->select('pid', 'player_name', 'tid_skin', 'tid_cape')
            ->get();
    }

    public function add(Request $request)
    {
        $user = Auth::user();

        $this->validate($request, [
            'player_name' => 'required|player_name|min:'.option('player_name_length_min').'|max:'.option('player_name_length_max')
        ]);

        event(new CheckPlayerExists($request->input('player_name')));

        if (! Player::where('player_name', $request->input('player_name'))->get()->isEmpty()) {
            return json(trans('user.player.add.repeated'), 6);
        }

        if ($user->getScore() < Option::get('score_per_player')) {
            return json(trans('user.player.add.lack-score'), 7);
        }

        event(new PlayerWillBeAdded($request->input('player_name')));

        $player = new Player;

        $player->uid           = $user->uid;
        $player->player_name   = $request->input('player_name');
        $player->tid_skin      = 0;
        $player->last_modified = get_datetime_string();
        $player->save();

        event(new PlayerWasAdded($player));

        $user->setScore(option('score_per_player'), 'minus');

        return json(trans('user.player.add.success', ['name' => $request->input('player_name')]), 0);
    }

    public function delete()
    {
        $playerName = $this->player->player_name;

        event(new PlayerWillBeDeleted($this->player));

        $this->player->delete();

        if (option('return_score')) {
            Auth::user()->setScore(Option::get('score_per_player'), 'plus');
        }

        event(new PlayerWasDeleted($playerName));

        return json(trans('user.player.delete.success', ['name' => $playerName]), 0);
    }

    public function show()
    {
        return response()->json($this->player->toArray());
    }

    public function rename(Request $request)
    {
        $this->validate($request, [
            'new_player_name' => 'required|player_name|min:'.option('player_name_length_min').'|max:'.option('player_name_length_max')
        ]);

        $newName = $request->input('new_player_name');

        if (! Player::where('player_name', $newName)->get()->isEmpty()) {
            return json(trans('user.player.rename.repeated'), 6);
        }

        $oldName = $this->player->player_name;

        $this->player->rename($newName);

        return json(trans('user.player.rename.success', ['old' => $oldName, 'new' => $newName]), 0);
    }

    /**
     * A wrapper of Player::setTexture().
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setTexture(Request $request)
    {
        foreach ($request->input('tid') as $key => $value) {
            $texture = Texture::find($value);

            if (! $texture) {
                return json(trans('skinlib.un-existent'), 6);
            }

            $fieldName = $texture->type == 'cape' ? 'tid_cape' : 'tid_skin';

            $this->player->setTexture([$fieldName => $value]);
        }

        return json(trans('user.player.set.success', ['name' => $this->player->player_name]), 0);
    }

    public function clearTexture(Request $request)
    {
        $types = array_filter(['skin', 'cape'], function ($type) use ($request) {
            return $request->input($type);
        });

        $this->player->clearTexture($types);

        return json(trans('user.player.clear.success', ['name' => $this->player->player_name]), 0);
    }

}

<?php

namespace App\Http\Controllers;

use View;
use Event;
use Utils;
use Option;
use App\Models\User;
use App\Models\Player;
use App\Models\Texture;
use App\Models\PlayerModel;
use Illuminate\Http\Request;
use App\Events\PlayerWasAdded;
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

        $player_name = $request->input('player_name');

        if (!PlayerModel::where('player_name', $player_name)->get()->isEmpty())
            return json('该角色名已经被其他人注册掉啦', 6);

        if ($this->user->getScore() < Option::get('score_per_player'))
            return json('积分不够添加角色啦', 7);

        $player                = new PlayerModel();
        $player->uid           = $this->user->uid;
        $player->player_name   = $player_name;
        $player->preference    = "default";
        $player->last_modified = Utils::getTimeFormatted();
        $player->save();

        Event::fire(new PlayerWasAdded($player));

        $this->user->setScore(Option::get('score_per_player'), 'minus');

        return json("成功添加了角色 $player_name", 0);
    }

    public function delete(Request $request)
    {
        $player_name = $this->player->player_name;

        if ($this->player->delete()) {
            $this->user->setScore(Option::get('score_per_player'), 'plus');

            return json("角色 $player_name 已被删除", 0);
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

        $new_player_name = $request->input('new_player_name');

        if (!PlayerModel::where('player_name', $new_player_name)->get()->isEmpty())
            return json('此角色名已被他人使用，换一个吧~', 6);

        $old_player_name = $this->player->player_name;
        $this->player->rename($new_player_name);

        return json("角色 $old_player_name 已更名为 $new_player_name", 0);
    }

    /**
     * A wrapper of Player::setTexture()
     */
    public function setTexture(Request $request)
    {
        $this->validate($request, [
            'tid' => 'required|integer'
        ]);

        if (!($texture = Texture::find($tid)))
            return json('材质不存在', 6);

        $field_name = "tid_".$texture->type;

        $this->player->setTexture([$field_name => $tid]);

        return json('材质已成功应用至角色 '.$this->player->player_name, 0);
    }

    public function clearTexture()
    {
        $this->player->clearTexture();

        return json('角色 '.$this->player->player_name.' 的材质已被成功重置', 0);
    }

    public function setPreference(Request $request)
    {
        $this->validate($request, [
            'preference' => 'required|preference'
        ]);

        $this->player->setPreference($request->preference);

        return json('角色 '.$this->player->player_name.' 的优先模型已更改至 '.$request->preference, 0);
    }

}

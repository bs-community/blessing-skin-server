<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use App\Models\User;
use App\Models\Player;
use App\Models\PlayerModel;
use App\Models\Texture;
use App\Exceptions\E;
use Validate;
use Utils;
use Option;
use View;

class PlayerController extends BaseController
{
    private $player = null;

    private $user   = null;

    public function __construct()
    {
        $this->user = new User(session('uid'));

        if (isset($_POST['pid'])) {
            $this->player = new Player($_POST['pid']);
            if (!$this->player)
                \Http::abort(404, '角色不存在');
        }
    }

    public function index()
    {
        return View::make('user.player')->with('players', $this->user->getPlayers()->toArray())->with('user', $this->user);
    }

    public function add()
    {
        $player_name = $_POST['player_name'];

        if (!isset($player_name))
            View::json('你还没有填写要添加的角色名哦', 1);

        if (!Validate::playerName($player_name))
        {
            $msg = "无效的角色名。角色名只能包含" . ((Option::get('allow_chinese_playername') == "1") ? "汉字、" : "")."字母、数字以及下划线";
            View::json($msg, 2);
        }

        if (!PlayerModel::where('player_name', $player_name)->get()->isEmpty())
            View::json('该角色名已经被其他人注册掉啦', 6);

        if ($this->user->getScore() < Option::get('score_per_player'))
            View::json('积分不够添加角色啦', 7);

        $player                = new PlayerModel();
        $player->uid           = $this->user->uid;
        $player->player_name   = $player_name;
        $player->preference    = "default";
        $player->last_modified = Utils::getTimeFormatted();
        $player->save();

        $this->user->setScore(Option::get('score_per_player'), 'minus');

        View::json('成功添加了角色 '.$player_name.'', 0);

    }

    public function delete()
    {
        $player_name = $this->player->model->player_name;
        $this->player->model->delete();

        $this->user->setScore(Option::get('score_per_player'), 'plus');

        View::json('角色 '.$player_name.' 已被删除', 0);
    }

    public function show()
    {
        echo json_encode($this->player->model->toArray(), JSON_NUMERIC_CHECK);
    }

    public function rename()
    {
        $new_player_name = Utils::getValue('new_player_name', $_POST);

        if (!$new_player_name)
            throw new E('非法参数', 1);

        if (!Validate::playerName($new_player_name))
        {
            $msg = "无效的角色名。角色名只能包含" . ((Option::get('allow_chinese_playername') == "1") ? "汉字、" : "")."字母、数字以及下划线";
            View::json($msg, 2);
        }

        if (!PlayerModel::where('player_name', $new_player_name)->get()->isEmpty())
            View::json('此角色名已被他人使用，换一个吧~', 6);

        $old_player_name = $this->player->model->player_name;
        $this->player->model->player_name = $new_player_name;
        $this->player->model->last_modified = Utils::getTimeFormatted();
        $this->player->model->save();

        View::json('角色 '.$old_player_name.' 已更名为 '.$_POST['new_player_name'], 0);
    }

    /**
     * A wrapper of Player::setTexture()
     */
    public function setTexture()
    {
        $tid = Utils::getValue('tid', $_POST);

        if (!is_numeric($tid))
            throw new E('非法参数', 1);

        if (!($texture = Texture::find($tid)))
            View::json('Unexistent texture.', 6);

        $field_name = "tid_".$texture->type;

        $this->player->model->$field_name = $tid;
        $this->player->model->last_modified = Utils::getTimeFormatted();
        $this->player->model->save();

        View::json('材质已成功应用至角色 '.$this->player->model->player_name.'', 0);
    }



    public function clearTexture()
    {
        $this->player->clearTexture();

        View::json('角色 '.$this->player->model->player_name.' 的材质已被成功重置', 0);
    }

    public function setPreference()
    {
        if (!isset($_POST['preference']) ||
            ($_POST['preference'] != "default" && $_POST['preference'] != "slim"))
        {
            throw new E('非法参数', 1);
        }

        $this->player->setPreference($_POST['preference']);

        View::json('角色 '.$this->player->player_name.' 的优先模型已更改至 '.$_POST['preference'], 0);
    }

}

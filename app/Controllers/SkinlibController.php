<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Texture;
use App\Exceptions\E;
use Validate;
use Option;
use Utils;
use View;
use Http;

class SkinlibController extends BaseController
{
    private $user = null;

    function __construct()
    {
        $this->user = isset($_SESSION['email']) ? new User($_SESSION['email']) : null;
    }

    public function index()
    {
        $filter = isset($_GET['filter']) ? $_GET['filter'] : "skin";

        $sort = isset($_GET['sort']) ? $_GET['sort'] : "time";
        $sort_by = ($sort == "time") ? "upload_at" : $sort;

        $page = isset($_GET['page']) ? $_GET['page'] : 1;

        if ($filter == "skin") {
            $textures = Texture::where(function($query) {
                $query->where('type', '=', 'steve')
                      ->orWhere('type', '=', 'alex');
            })->orderBy($sort_by, 'desc');

            $total_pages = ceil($textures->count() / 20);

        } elseif ($filter == "user") {
            $uid = isset($_GET['uid']) ? $_GET['uid'] : 0;

            if (!is_null($this->user) && $uid == $this->user->uid) {
                // show private textures when show uploaded textures of current user
                $textures = Texture::where('uploader', $uid)->orderBy($sort_by, 'desc');
                $total_pages = ceil($textures->count() / 20);
            } else {
                $textures = Texture::where('uploader', $uid)->orderBy($sort_by, 'desc');
                $total_pages = ceil($textures->count() / 20);
            }

        } else {
            $textures = Texture::where('type', $filter)->orderBy($sort_by, 'desc');
            $total_pages = ceil($textures->count() / 20);
        }

        if (is_null($this->user) || (!is_null($this->user) && !$this->user->is_admin))
            $textures = $textures->where('public', '1');

        $textures = $textures->skip(($page - 1) * 20)->take(20)->get();

        echo View::make('skinlib.index')->with('user', $this->user)
                                        ->with('sort', $sort)
                                        ->with('filter', $filter)
                                        ->with('textures', $textures)
                                        ->with('page', $page)
                                        ->with('total_pages', $total_pages)
                                        ->render();
    }

    public function search()
    {
        $q = isset($_GET['q']) ? $_GET['q'] : "";

        $filter = isset($_GET['filter']) ? $_GET['filter'] : "skin";

        $sort = isset($_GET['sort']) ? $_GET['sort'] : "time";
        $sort_by = ($sort == "time") ? "upload_at" : $sort;

        if ($filter == "skin") {
            $textures = Texture::like('name', $q)->where(function($query) use ($q) {
                $query->where('public', '=', '1')
                      ->where('type', '=', 'steve')
                      ->orWhere('type', '=', 'alex');
            })->orderBy($sort_by, 'desc')->get();
        } else {
            $textures = Texture::like('name', $q)
                                ->where('type', $filter)
                                ->where('public', '1')
                                ->orderBy($sort_by, 'desc')->get();
        }

        echo View::make('skinlib.search')->with('user', $this->user)
                                        ->with('sort', $sort)
                                        ->with('filter', $filter)
                                        ->with('q', $q)
                                        ->with('textures', $textures)->render();
    }

    public function show()
    {
        if (!isset($_GET['tid'])) Http::abort(404, 'No specified tid.');
        $texture = Texture::find($_GET['tid']);
        if (!$texture) Http::abort(404, '请求的材质已经被删除');

        if ($texture->public == "0") {
            if (is_null($this->user) || ($this->user->uid != $texture->uploader && !$this->user->is_admin))
                Http::abort(404, '请求的材质已经设为隐私，仅上传者和管理员可查看');
        }

        echo View::make('skinlib.show')->with('texture', $texture)->with('with_out_filter', true)->with('user', $this->user)->render();
    }

    public function info($tid)
    {
        echo json_encode(Texture::find($tid)->toArray());
    }

    public function upload()
    {
        echo View::make('skinlib.upload')->with('user', $this->user)->with('with_out_filter', true)->render();
    }

    public function handleUpload()
    {
        $this->checkUpload(isset($_POST['type']) ? $_POST['type'] : "");

        $t            = new Texture();
        $t->name      = $_POST['name'];
        $t->type      = $_POST['type'];
        $t->hash      = \Storage::upload($_FILES['file']);
        $t->size      = ceil($_FILES['file']['size'] / 1024);
        $t->public    = ($_POST['public'] == 'true') ? "1" : "0";
        $t->uploader  = $this->user->uid;
        $t->upload_at = Utils::getTimeFormatted();

        if ($this->user->getScore() / Option::get('score_per_storage') < $t->size)
            View::json('积分不够啦', 7);

        $results = Texture::where('hash', $t->hash)->get();
        if (!$results->isEmpty())
        {
            foreach ($results as $result) {
                if ($result->type == $t->type) {
                    View::json([
                        'errno' => 0,
                        'msg' => '已经有人上传过这个材质了，直接添加到衣柜使用吧~',
                        'tid' => $result->tid
                    ]);
                }
            }
        }

        $t->save();

        $this->user->setScore($t->size, 'minus');

        if ($this->user->closet->add($t->tid, $t->name)) {
            $t = Texture::find($t->tid);
            $t->likes += 1;
            $t->save();

            View::json([
                'errno' => 0,
                'msg'   => '材质 '.$_POST['name'].' 上传成功',
                'tid'   => $t->tid
            ]);
        }
    }

    public function delete()
    {
        Validate::checkPost(['tid']);

        $result = Texture::find($_POST['tid']);

        if (!$result)
            View::json('Unexistent texture.', 1);

        if ($result->uploader != $this->user->uid && !$this->user->is_admin)
            View::json('你不是这个材质的上传者哦', 1);

        // check if file occupied
        if (Texture::where('hash', $result['hash'])->count() == 1)
            \Storage::remove("./textures/".$result['hash']);

        $this->user->setScore($result->size * Option::get('score_per_storage'), 'plus');

        if ($result->delete())
            View::json('材质已被成功删除', 0);
    }

    public function privacy($tid)
    {
        $t = Texture::find($tid);

        if (!$t) View::json('Unexistent texture.', 1);

        if ($t->uploader != $this->user->uid && !$this->user->is_admin)
            View::json('你不是这个材质的上传者哦', 1);

        if ($t->setPrivacy(!$t->public)) {
            View::json([
                'errno'  => 0,
                'msg'    => '材质已被设为'.($t->public == "0" ? "隐私" : "公开"),
                'public' => $t->public
            ]);
        }
    }

    public function rename() {
        Validate::checkPost(['tid', 'new_name']);
        Validate::textureName($_POST['new_name']);

        $t = Texture::find($_POST['tid']);

        if (!$t) View::json('材质不存在', 1);

        if ($t->uploader != $this->user->uid && !$this->user->is_admin)
            View::json('你不是这个材质的上传者哦', 1);

        $t->name = $_POST['new_name'];

        if ($t->save()) {
            View::json('材质名称已被成功设置为'.$_POST['new_name'], 0);
        }
    }

    private function checkUpload($type)
    {
        Validate::textureName(Utils::getValue('name', $_POST));

        if (!Utils::getValue('file', $_FILES))
            View::json('你还没有选择任何文件哟', 1);

        if (!isset($_POST['public']) || ($_POST['public'] != 0 && $_POST['public'] != 1))
            View::json('Invalid parameters.', 1);

        if ($_FILES['file']['type'] == "image/png" || $_FILES['file']['type'] == "image/x-png")
        {
            // if error occured while uploading file
            if ($_FILES['file']["error"] > 0)
                View::json($_FILES['file']["error"], 1);

            $size = getimagesize($_FILES['file']["tmp_name"]);
            $ratio = $size[0] / $size[1];

            if ($type == "steve" || $type == "alex") {
                if ($ratio != 2 && $ratio != 1)
                    View::json("不是有效的皮肤文件（宽 {$size[0]}，高 {$size[1]}）", 1);
            } elseif ($type == "cape") {
                if ($ratio != 2)
                    View::json("不是有效的披风文件（宽 {$size[0]}，高 {$size[1]}）", 1);
            } else {
                View::json('Invalid parameters.', 1);
            }

        } else {
            if (Utils::getValue('file', $_FILES)) {
                View::json('文件格式不对哦', 1);
            } else {
                View::json('No file selected.', 1);
            }
        }

        return true;
    }

}

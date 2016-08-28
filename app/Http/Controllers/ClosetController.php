<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use App\Models\User;
use App\Models\Texture;
use App\Models\Closet;
use App\Models\ClosetModel;
use App\Exceptions\E;
use View;
use Option;

class ClosetController extends BaseController
{
    private $closet;

    public function __construct()
    {
        $this->closet = new Closet(session('uid'));
    }

    public function index()
    {
        $category = isset($_GET['category']) ? $_GET['category'] : "skin";

        $page = isset($_GET['page']) ? $_GET['page'] : 1;

        $items = array_slice($this->closet->getItems($category), ($page-1)*6, 6);

        $total_pages = ceil(count($this->closet->getItems($category)) / 6);

        echo View::make('user.closet')->with('items', $items)
                                      ->with('page', $page)
                                      ->with('category', $category)
                                      ->with('total_pages', $total_pages)
                                      ->with('user', (new User(session('uid'))))
                                      ->render();
    }

    public function info()
    {
        View::json($this->closet->getItems());
    }

    public function add()
    {
        \Validate::checkPost(['tid', 'name']);

        if ($this->closet->add($_POST['tid'], $_POST['name'])) {
            $t = Texture::find($_POST['tid']);
            $t->likes += 1;
            $t->save();

            View::json('材质 '.$_POST['name'].' 收藏成功~', 0);
        }
    }

    public function remove()
    {
        if (!is_numeric(\Utils::getValue('tid', $_POST)))
            throw new E('非法参数', 1);

        if ($this->closet->remove($_POST['tid'])) {
            $t = Texture::find($_POST['tid']);
            $t->likes = $t->likes - 1;
            $t->save();

            View::json('材质已从衣柜中移除', 0);
        }
    }

}

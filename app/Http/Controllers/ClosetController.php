<?php

namespace App\Http\Controllers;

use View;
use Option;
use App\Models\User;
use App\Models\Closet;
use App\Models\Texture;
use App\Models\ClosetModel;
use Illuminate\Http\Request;
use App\Exceptions\PrettyPageException;

class ClosetController extends Controller
{
    /**
     * Instance of Closet.
     *
     * @var \App\Models\Closet
     */
    private $closet;

    public function __construct()
    {
        $this->closet = new Closet(session('uid'));
    }

    public function index(Request $request)
    {
        $category = $request->input('category', 'skin');
        $page     = $request->input('page', 1);

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
        return json($this->closet->getItems());
    }

    public function add(Request $request)
    {
        $this->validate($request, [
            'tid'  => 'required|integer',
            'name' => 'required|nickname',
        ]);

        if ($this->closet->add($request->tid, $request->name)) {
            $t = Texture::find($request->tid);
            $t->likes += 1;
            $t->save();

            return json('材质 '.$request->input('name').' 收藏成功~', 0);
        } else {
            return json('你已经收藏过这个材质啦', 1);
        }
    }

    public function remove(Request $request)
    {
        $this->validate($request, [
            'tid'  => 'required|integer'
        ]);

        if ($this->closet->remove($request->tid)) {
            $t = Texture::find($request->tid);
            $t->likes = $t->likes - 1;
            $t->save();

            return json('材质已从衣柜中移除', 0);
        } else {
            return json('衣柜中不存在此材质', 0);
        }
    }

}

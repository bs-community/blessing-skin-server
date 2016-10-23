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
use App\Services\Repositories\UserRepository;

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

    public function index(Request $request, UserRepository $users)
    {
        $category = $request->input('category', 'skin');
        $page     = $request->input('page', 1);
        $page     = $page <= 0 ? 1 : $page;
        $q        = $request->input('q', null);

        if ($q) {
            $result = [];

            foreach ($this->closet->getItems() as $item) {
                if (strstr($item->name, $q)) {
                    $result[] = $item;
                }
            }

            $items = $result;
        } else {
            $items = $this->closet->getItems($category);
        }

        // pagination
        $items = array_slice($items, ($page-1)*6, 6);

        $total_pages = ceil(count($items) / 6);

        echo View::make('user.closet')->with('items', $items)
                                      ->with('page', $page)
                                      ->with('q', $q)
                                      ->with('category', $category)
                                      ->with('total_pages', $total_pages)
                                      ->with('user', $users->get(session('uid')))
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
            'name' => 'required|no_special_chars'
        ]);

        if ($this->closet->add($request->tid, $request->name)) {
            $t = Texture::find($request->tid);
            $t->likes += 1;
            $t->save();

            return json(trans('user.closet.add.success', ['name' => $request->input('name')]), 0);
        } else {
            return json(trans('user.closet.add.repeated'), 1);
        }
    }

    public function rename(Request $request)
    {
        $this->validate($request, [
            'tid' => 'required|integer',
            'new_name' => 'required|no_special_chars'
        ]);

        if ($this->closet->rename($request->tid, $request->new_name)) {
            return json(trans('user.closet.rename.success', ['name' => $request->new_name]), 0);
        } else {
            return json(trans('user.closet.remove.non-existent'), 0);
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

            return json(trans('user.closet.remove.success'), 0);
        } else {
            return json(trans('user.closet.remove.non-existent'), 0);
        }
    }

}

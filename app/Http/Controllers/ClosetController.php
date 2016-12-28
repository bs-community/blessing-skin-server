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

        $items = [];

        if ($q) {
            foreach (['skin', 'cape'] as $category) {
                // do search
                foreach ($this->closet->getItems($category) as $item) {
                    if (strstr($item->name, $q)) {
                        $items[$category][] = $item;
                    }
                }
            }
        } else {
            $items['skin'] = $this->closet->getItems('skin');
            $items['cape'] = $this->closet->getItems('cape');
        }

        // pagination
        $total_pages = [];

        foreach ($items as $key => $value) {
            $total_pages[] = ceil(count($items[$key]) / 6);
            $items[$key] = array_slice($value, ($page-1)*6, 6);
        }

        return view('user.closet')->with('items', $items)
                                  ->with('page', $page)
                                  ->with('q', $q)
                                  ->with('category', $category)
                                  ->with('total_pages', max($total_pages))
                                  ->with('user', $users->get(session('uid')));
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

        if (app('user.current')->getScore() < option('score_per_closet_item', null, false)) {
            return json(trans('user.closet.add.lack-score'), 7);
        }

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

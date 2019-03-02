<?php

namespace App\Http\Controllers;

use View;
use Option;
use App\Models\User;
use App\Models\Closet;
use App\Models\Texture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $this->middleware(function ($request, $next) {
            $this->closet = new Closet(Auth::id());

            return $next($request);
        });
    }

    public function index()
    {
        return view('user.closet')->with('user', Auth::user());
    }

    public function getClosetData(Request $request)
    {
        $category = $request->input('category', 'skin');
        $page = abs($request->input('page', 1));
        $per_page = (int) $request->input('perPage', 6);
        $q = $request->input('q', null);

        $per_page = $per_page > 0 ? $per_page : 6;

        $items = collect();

        if ($q) {
            // Do search
            $items = $this->closet->getItems($category)->filter(function ($item) use ($q) {
                return stristr($item['name'], $q);
            });
        } else {
            $items = $this->closet->getItems($category);
        }

        // Pagination
        $total_pages = ceil($items->count() / $per_page);

        return response()->json([
            'category'    => $category,
            'items'       => $items->forPage($page, $per_page)->values(),
            'total_pages' => $total_pages,
        ]);
    }

    public function add(Request $request)
    {
        $this->validate($request, [
            'tid'  => 'required|integer',
            'name' => 'required|no_special_chars',
        ]);

        $currentUser = Auth::user();

        if ($currentUser->getScore() < option('score_per_closet_item')) {
            return json(trans('user.closet.add.lack-score'), 7);
        }

        $tid = $request->tid;
        if (! Texture::find($tid)) {
            return json(trans('user.closet.add.not-found'), 1);
        }

        if ($this->closet->add($tid, $request->name)) {
            $t = Texture::find($tid);
            $t->likes += 1;
            $t->save();

            $this->closet->save();

            $currentUser->setScore(option('score_per_closet_item'), 'minus');

            return json(trans('user.closet.add.success', ['name' => $request->input('name')]), 0);
        } else {
            return json(trans('user.closet.add.repeated'), 1);
        }
    }

    public function rename(Request $request)
    {
        $this->validate($request, [
            'tid' => 'required|integer',
            'new_name' => 'required|no_special_chars',
        ]);

        if ($this->closet->rename($request->tid, $request->new_name)) {
            $this->closet->save();

            return json(trans('user.closet.rename.success', ['name' => $request->new_name]), 0);
        } else {
            return json(trans('user.closet.remove.non-existent'), 1);
        }
    }

    public function remove(Request $request)
    {
        $this->validate($request, [
            'tid'  => 'required|integer',
        ]);

        if ($this->closet->remove($request->tid)) {
            $t = Texture::find($request->tid);
            $t->likes = $t->likes - 1;
            $t->save();

            $this->closet->save();

            if (option('return_score')) {
                Auth::user()->setScore(option('score_per_closet_item'), 'plus');
            }

            return json(trans('user.closet.remove.success'), 0);
        } else {
            return json(trans('user.closet.remove.non-existent'), 1);
        }
    }
}

<?php

namespace App\Http\Controllers;

use View;
use Option;
use App\Models\User;
use App\Models\Texture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClosetController extends Controller
{
    public function index()
    {
        return view('user.closet')
            ->with('extra', ['unverified' => option('require_verification') && ! $user->verified]);
    }

    public function getClosetData(Request $request)
    {
        $category = $request->input('category', 'skin');
        $page = abs($request->input('page', 1));
        $perPage = (int) $request->input('perPage', 6);
        $q = $request->input('q', null);

        $perPage = $perPage > 0 ? $perPage : 6;

        $user = auth()->user();
        $closet = $user->closet();

        if ($category == 'cape') {
            $closet = $closet->where('type', 'cape');
        } else {
            $closet = $closet->where(function ($query) {
                return $query->where('type', 'steve')->orWhere('type', 'alex');
            });
        }

        if ($q) {
            $closet = $closet->where('item_name', 'like', "%$q%");
        }

        $closet->offset(($page - 1) * $perPage)->limit($perPage);

        // Pagination
        $items = $closet->get()->map(function ($t) {
            $t->name = $t->pivot->item_name;

            return $t;
        });
        $totalPages = ceil($items->count() / $perPage);

        return response()->json([
            'category'    => $category,
            'items'       => $items,
            'total_pages' => $totalPages,
        ]);
    }

    public function add(Request $request)
    {
        $this->validate($request, [
            'tid'  => 'required|integer',
            'name' => 'required|no_special_chars',
        ]);

        $user = Auth::user();

        if ($user->score < option('score_per_closet_item')) {
            return json(trans('user.closet.add.lack-score'), 7);
        }

        $tid = $request->tid;
        $texture = Texture::find($tid);
        if (! $texture) {
            return json(trans('user.closet.add.not-found'), 1);
        }

        if ($user->closet()->where('tid', $request->tid)->count() > 0) {
            return json(trans('user.closet.add.repeated'), 1);
        }

        $user->closet()->attach($tid, ['item_name' => $request->name]);
        $user->setScore(option('score_per_closet_item'), 'minus');

        $uploader = User::find($texture->uploader);
        if ($uploader && $uploader->uid != $user->uid) {
            $uploader->score += option('score_award_per_like', 0);
            $uploader->save();
        }

        return json(trans('user.closet.add.success', ['name' => $request->input('name')]), 0);
    }

    public function rename(Request $request)
    {
        $this->validate($request, [
            'tid' => 'required|integer',
            'new_name' => 'required|no_special_chars',
        ]);

        $user = auth()->user();

        if ($user->closet()->where('tid', $request->tid)->count() == 0) {
            return json(trans('user.closet.remove.non-existent'), 1);
        }

        $user->closet()->updateExistingPivot($request->tid, ['item_name' => $request->new_name]);

        return json(trans('user.closet.rename.success', ['name' => $request->new_name]), 0);
    }

    public function remove(Request $request)
    {
        $this->validate($request, [
            'tid' => 'required|integer',
        ]);
        $tid = $request->tid;

        $user = auth()->user();

        if ($user->closet()->where('tid', $tid)->count() == 0) {
            return json(trans('user.closet.remove.non-existent'), 1);
        }

        $user->closet()->detach($tid);

        if (option('return_score')) {
            $user->setScore(option('score_per_closet_item'), 'plus');
        }

        $uploader = User::find(Texture::find($tid)->uploader);
        $uploader->score -= option('score_award_per_like', 0);
        $uploader->save();

        return json(trans('user.closet.remove.success'), 0);
    }
}

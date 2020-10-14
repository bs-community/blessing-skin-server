<?php

namespace App\Http\Controllers;

use App\Models\Texture;
use App\Models\User;
use Auth;
use Blessing\Filter;
use Blessing\Rejection;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ClosetController extends Controller
{
    public function index(Filter $filter)
    {
        $grid = [
            'layout' => [
                ['md-8', 'md-4'],
            ],
            'widgets' => [
                [
                    [
                        'user.widgets.email-verification',
                        'user.widgets.closet.list',
                    ],
                    ['shared.previewer'],
                ],
            ],
        ];
        $grid = $filter->apply('grid:user.closet', $grid);

        return view('user.closet')
            ->with('grid', $grid)
            ->with('extra', [
                'unverified' => option('require_verification') && !auth()->user()->verified,
                'rule' => trans('user.player.player-name-rule.'.option('player_name_rule')),
                'length' => trans(
                    'user.player.player-name-length',
                    ['min' => option('player_name_length_min'), 'max' => option('player_name_length_max')]
                ),
            ]);
    }

    public function getClosetData(Request $request)
    {
        $category = $request->input('category', 'skin');
        /** @var User */
        $user = auth()->user();

        return $user
            ->closet()
            ->when(
                $category === 'cape',
                fn (Builder $query) => $query->where('type', 'cape'),
                fn (Builder $query) => $query->whereIn('type', ['steve', 'alex']),
            )
            ->when(
                $request->input('q'),
                fn (Builder $query, $search) => $query->like('item_name', $search)
            )
            ->paginate((int) $request->input('perPage', 6));
    }

    public function allIds()
    {
        /** @var User */
        $user = auth()->user();

        return $user->closet()->pluck('texture_tid');
    }

    public function add(
        Request $request,
        Dispatcher $dispatcher,
        Filter $filter
    ) {
        ['tid' => $tid, 'name' => $name] = $request->validate([
            'tid' => 'required|integer',
            'name' => 'required',
        ]);

        /** @var User */
        $user = Auth::user();
        $name = $filter->apply('add_closet_item_name', $name, [$tid]);
        $dispatcher->dispatch('closet.adding', [$tid, $name, $user]);

        $can = $filter->apply('can_add_closet_item', true, [$tid, $name]);
        if ($can instanceof Rejection) {
            return json($can->getReason(), 1);
        }

        if ($user->score < option('score_per_closet_item')) {
            return json(trans('user.closet.add.lack-score'), 1);
        }

        $tid = $request->tid;
        $texture = Texture::find($tid);
        if (!$texture) {
            return json(trans('user.closet.add.not-found'), 1);
        }

        if (!$texture->public && ($texture->uploader != $user->uid && !$user->isAdmin())) {
            return json(trans('skinlib.show.private'), 1);
        }

        if ($user->closet()->where('tid', $request->tid)->count() > 0) {
            return json(trans('user.closet.add.repeated'), 1);
        }

        $user->closet()->attach($tid, ['item_name' => $request->name]);
        $user->score -= option('score_per_closet_item');
        $user->save();

        $texture->likes++;
        $texture->save();

        $dispatcher->dispatch('closet.added', [$texture, $name, $user]);

        $uploader = User::find($texture->uploader);
        if ($uploader && $uploader->uid != $user->uid) {
            $uploader->score += option('score_award_per_like', 0);
            $uploader->save();
        }

        return json(trans('user.closet.add.success', ['name' => $request->input('name')]), 0);
    }

    public function rename(
        Request $request,
        Dispatcher $dispatcher,
        Filter $filter,
        $tid
    ) {
        ['name' => $name] = $request->validate(['name' => 'required']);
        /** @var User */
        $user = auth()->user();

        $name = $filter->apply('rename_closet_item_name', $name, [$tid]);
        $dispatcher->dispatch('closet.renaming', [$tid, $name, $user]);

        $item = $user->closet()->find($tid);
        if (empty($item)) {
            return json(trans('user.closet.remove.non-existent'), 1);
        }
        $previousName = $item->pivot->item_name;

        $can = $filter->apply('can_rename_closet_item', true, [$item, $name]);
        if ($can instanceof Rejection) {
            return json($can->getReason(), 1);
        }

        $user->closet()->updateExistingPivot($tid, ['item_name' => $name]);

        $dispatcher->dispatch('closet.renamed', [$item, $previousName, $user]);

        return json(trans('user.closet.rename.success', ['name' => $name]), 0);
    }

    public function remove(Dispatcher $dispatcher, Filter $filter, $tid)
    {
        /** @var User */
        $user = auth()->user();

        $dispatcher->dispatch('closet.removing', [$tid, $user]);

        $item = $user->closet()->find($tid);
        if (empty($item)) {
            return json(trans('user.closet.remove.non-existent'), 1);
        }

        $can = $filter->apply('can_remove_closet_item', true, [$item]);
        if ($can instanceof Rejection) {
            return json($can->getReason(), 1);
        }

        $user->closet()->detach($tid);

        if (option('return_score')) {
            $user->score += option('score_per_closet_item');
            $user->save();
        }

        $texture = Texture::find($tid);
        $texture->likes--;
        $texture->save();

        $dispatcher->dispatch('closet.removed', [$texture, $user]);

        $uploader = User::find($texture->uploader);
        $uploader->score -= option('score_award_per_like', 0);
        $uploader->save();

        return json(trans('user.closet.remove.success'), 0);
    }
}

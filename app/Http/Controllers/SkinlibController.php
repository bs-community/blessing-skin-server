<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Texture;
use App\Models\User;
use Auth;
use Blessing\Filter;
use Blessing\Rejection;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Parsedown;
use Storage;

class SkinlibController extends Controller
{
    public function library(Request $request)
    {
        $user = Auth::user();

        // Available filters: skin, steve, alex, cape
        $type = $request->input('filter', 'skin');
        $uploader = $request->input('uploader');
        $keyword = $request->input('keyword');
        $sort = $request->input('sort', 'time');
        $sortBy = $sort == 'time' ? 'upload_at' : $sort;

        return Texture::orderBy($sortBy, 'desc')
            ->when($type === 'skin', function (Builder $query) {
                return $query->whereIn('type', ['steve', 'alex']);
            }, function (Builder $query) use ($type) {
                return $query->where('type', $type);
            })
            ->when($keyword, function (Builder $query, $keyword) {
                return $query->like('name', $keyword);
            })
            ->when($uploader, function (Builder $query, $uploader) {
                return $query->where('uploader', $uploader);
            })
            ->when($user, function (Builder $query, User $user) {
                if (!$user->isAdmin()) {
                    // use closure-style `where` clause to lift up SQL priority
                    return $query->where(function (Builder $query) use ($user) {
                        $query
                            ->where('public', true)
                            ->orWhere('uploader', $user->uid);
                    });
                }
            }, function (Builder $query) {
                // show public textures only to anonymous visitors
                return $query->where('public', true);
            })
            ->join('users', 'uid', 'uploader')
            ->select(['tid', 'name', 'type', 'uploader', 'public', 'likes', 'nickname'])
            ->paginate(20);
    }

    public function show(Filter $filter, $tid)
    {
        $texture = Texture::find($tid);
        /** @var User */
        $user = Auth::user();
        /** @var FilesystemAdapter */
        $disk = Storage::disk('textures');

        if (!$texture || $texture && $disk->missing($texture->hash)) {
            if (option('auto_del_invalid_texture')) {
                if ($texture) {
                    $texture->delete();
                }

                abort(404, trans('skinlib.show.deleted'));
            }
            abort(404, trans('skinlib.show.deleted').trans('skinlib.show.contact-admin'));
        }

        if (!$texture->public) {
            if (!Auth::check() || ($user->uid != $texture->uploader && !$user->isAdmin())) {
                abort(option('status_code_for_private'), trans('skinlib.show.private'));
            }
        }

        $badges = [];
        $uploader = $texture->owner;
        if ($uploader) {
            if ($uploader->isAdmin()) {
                $badges[] = ['text' => 'STAFF', 'color' => 'primary'];
            }

            $badges = $filter->apply('user_badges', $badges, [$uploader]);
        }

        $grid = [
            'layout' => [
                ['md-8', 'md-4'],
            ],
            'widgets' => [
                [
                    ['shared.previewer'],
                    ['skinlib.widgets.show.side'],
                ],
            ],
        ];
        $grid = $filter->apply('grid:skinlib.show', $grid);

        return view('skinlib.show')
            ->with('texture', $texture)
            ->with('grid', $grid)
            ->with('extra', [
                'download' => option('allow_downloading_texture'),
                'currentUid' => $user ? $user->uid : 0,
                'admin' => $user && $user->isAdmin(),
                'inCloset' => $user && $user->closet()->where('tid', $texture->tid)->count() > 0,
                'uploaderExists' => (bool) $uploader,
                'nickname' => optional($uploader)->nickname ?? trans('general.unexistent-user'),
                'report' => intval(option('reporter_score_modification', 0)),
                'badges' => $badges,
            ]);
    }

    public function info(Texture $texture)
    {
        return $texture;
    }

    public function upload(Filter $filter)
    {
        $grid = [
            'layout' => [
                ['md-6', 'md-6'],
            ],
            'widgets' => [
                [
                    ['skinlib.widgets.upload.input'],
                    ['shared.previewer'],
                ],
            ],
        ];
        $grid = $filter->apply('grid:skinlib.upload', $grid);

        $parsedown = new Parsedown();

        return view('skinlib.upload')
            ->with('grid', $grid)
            ->with('extra', [
                'rule' => ($regexp = option('texture_name_regexp'))
                    ? trans('skinlib.upload.name-rule-regexp', compact('regexp'))
                    : trans('skinlib.upload.name-rule'),
                'privacyNotice' => trans(
                    'skinlib.upload.private-score-notice',
                    ['score' => option('private_score_per_storage')]
                ),
                'score' => (int) auth()->user()->score,
                'scorePublic' => (int) option('score_per_storage'),
                'scorePrivate' => (int) option('private_score_per_storage'),
                'closetItemCost' => (int) option('score_per_closet_item'),
                'award' => (int) option('score_award_per_texture'),
                'contentPolicy' => $parsedown->text(option_localized('content_policy')),
            ]);
    }

    public function handleUpload(
        Request $request,
        Filter $filter,
        Dispatcher $dispatcher
    ) {
        $file = $request->file('file');
        if ($file && !$file->isValid()) {
            Log::error($file->getErrorMessage());
        }

        $data = $request->validate([
            'name' => [
                'required',
                option('texture_name_regexp') ? 'regex:'.option('texture_name_regexp') : 'string',
            ],
            'file' => 'required|mimes:png|max:'.option('max_upload_file_size'),
            'type' => ['required', Rule::in(['steve', 'alex', 'cape'])],
            'public' => 'required|boolean',
        ]);

        $file = $filter->apply('uploaded_texture_file', $file);

        $name = $data['name'];
        $name = $filter->apply('uploaded_texture_name', $name, [$file]);

        $can = $filter->apply('can_upload_texture', true, [$file, $name]);
        if ($can instanceof Rejection) {
            return json($can->getReason(), 1);
        }

        $type = $data['type'];
        $size = getimagesize($file);
        $ratio = $size[0] / $size[1];
        if ($type == 'steve' || $type == 'alex') {
            if ($ratio != 2 && $ratio != 1) {
                $message = trans('skinlib.upload.invalid-size', [
                    'type' => trans('general.skin'),
                    'width' => $size[0],
                    'height' => $size[1],
                ]);

                return json($message, 1);
            }
            if ($size[0] % 64 != 0 || $size[1] % 32 != 0) {
                $message = trans('skinlib.upload.invalid-hd-skin', [
                    'type' => trans('general.skin'),
                    'width' => $size[0],
                    'height' => $size[1],
                ]);

                return json($message, 1);
            }
        } elseif ($type == 'cape') {
            if ($ratio != 2) {
                $message = trans('skinlib.upload.invalid-size', [
                    'type' => trans('general.cape'),
                    'width' => $size[0],
                    'height' => $size[1],
                ]);

                return json($message, 1);
            }
        }

        $hash = hash_file('sha256', $file);
        $hash = $filter->apply('uploaded_texture_hash', $hash, [$file]);

        $duplicated = Texture::where('hash', $hash)->where('public', true)->first();
        if ($duplicated) {
            // if the texture already uploaded was set to private,
            // then allow to re-upload it.
            return json(trans('skinlib.upload.repeated'), 2, ['tid' => $duplicated->tid]);
        }

        /** @var User */
        $user = Auth::user();

        $size = ceil($file->getSize() / 1024);
        $isPublic = is_string($data['public'])
            ? $data['public'] === '1'
            : $data['public'];
        $cost = $size * (
            $isPublic
            ? option('score_per_storage')
            : option('private_score_per_storage')
        );
        $cost += option('score_per_closet_item');
        $cost -= option('score_award_per_texture', 0);
        if ($user->score < $cost) {
            return json(trans('skinlib.upload.lack-score'), 1);
        }

        $dispatcher->dispatch('texture.uploading', [$file, $name, $hash]);

        $texture = new Texture();
        $texture->name = $name;
        $texture->type = $type;
        $texture->hash = $hash;
        $texture->size = $size;
        $texture->public = $isPublic;
        $texture->uploader = $user->uid;
        $texture->likes = 1;
        $texture->save();

        /** @var FilesystemAdapter */
        $disk = Storage::disk('textures');
        if ($disk->missing($hash)) {
            $disk->putFile($hash, $file);
        }

        $user->score -= $cost;
        $user->closet()->attach($texture->tid, ['item_name' => $name]);
        $user->save();

        $dispatcher->dispatch('texture.uploaded', [$texture, $file]);

        return json(trans('skinlib.upload.success', ['name' => $name]), 0, [
            'tid' => $texture->tid,
        ]);
    }

    public function delete(Request $request)
    {
        $texture = Texture::find($request->tid);
        /** @var User */
        $user = Auth::user();

        if (!$texture) {
            return json(trans('skinlib.non-existent'), 1);
        }

        if ($texture->uploader != $user->uid && !$user->isAdmin()) {
            return json(trans('skinlib.no-permission'), 1);
        }

        // check if file occupied
        if (Texture::where('hash', $texture->hash)->count() == 1) {
            Storage::disk('textures')->delete($texture->hash);
        }

        $texture->delete();

        return json(trans('skinlib.delete.success'), 0);
    }

    public function privacy(Request $request)
    {
        $t = Texture::find($request->input('tid'));
        $user = $request->user();

        if (!$t) {
            return json(trans('skinlib.non-existent'), 1);
        }

        if ($t->uploader != $user->uid && !$user->isAdmin()) {
            return json(trans('skinlib.no-permission'), 1);
        }

        $uploader = User::find($t->uploader);
        $score_diff = $t->size * (option('private_score_per_storage') - option('score_per_storage')) * ($t->public ? -1 : 1);
        if ($t->public && option('take_back_scores_after_deletion', true)) {
            $score_diff -= option('score_award_per_texture', 0);
        }
        if ($uploader->score + $score_diff < 0) {
            return json(trans('skinlib.upload.lack-score'), 1);
        }

        $type = $t->type == 'cape' ? 'cape' : 'skin';
        Player::where("tid_$type", $t->tid)
            ->where('uid', '<>', session('uid'))
            ->update(["tid_$type" => 0]);

        $t->likers()->get()->each(function ($user) use ($t) {
            $user->closet()->detach($t->tid);
            if (option('return_score')) {
                $user->score += option('score_per_closet_item');
                $user->save();
            }
            $t->likes--;
        });

        $uploader->score += $score_diff;
        $uploader->save();

        $t->public = !$t->public;
        $t->save();

        return json(
            trans('skinlib.privacy.success', ['privacy' => (!$t->public ? trans('general.private') : trans('general.public'))]),
            0
        );
    }

    public function rename(Request $request)
    {
        $request->validate([
            'tid' => 'required|integer',
            'new_name' => 'required',
        ]);
        $user = $request->user();
        $t = Texture::find($request->input('tid'));

        if (!$t) {
            return json(trans('skinlib.non-existent'), 1);
        }

        if ($t->uploader != $user->uid && !$user->isAdmin()) {
            return json(trans('skinlib.no-permission'), 1);
        }

        $t->name = $request->input('new_name');

        if ($t->save()) {
            return json(trans('skinlib.rename.success', ['name' => $request->input('new_name')]), 0);
        }
    }

    // @codeCoverageIgnore

    public function model(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'tid' => 'required|integer',
            'model' => 'required|in:steve,alex,cape',
        ]);

        $t = Texture::find($request->input('tid'));

        if (!$t) {
            return json(trans('skinlib.non-existent'), 1);
        }

        if ($t->uploader != $user->uid && !$user->isAdmin()) {
            return json(trans('skinlib.no-permission'), 1);
        }

        $t->type = $request->input('model');
        $t->save();

        return json(trans('skinlib.model.success', ['model' => $data['model']]), 0);
    }
}

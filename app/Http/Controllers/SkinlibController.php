<?php

namespace App\Http\Controllers;

use App\Models\Texture;
use App\Models\User;
use Auth;
use Blessing\Filter;
use Blessing\Rejection;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Storage;

class SkinlibController extends Controller
{
    public function __construct()
    {
        $this->middleware(function (Request $request, $next) {
            /** @var User */
            $user = $request->user();
            /** @var Texture */
            $texture = $request->route('texture');

            if ($texture->uploader != $user->uid && !$user->isAdmin()) {
                return json(trans('skinlib.no-permission'), 1)
                    ->setStatusCode(403);
            }

            return $next($request);
        })->only(['rename', 'privacy', 'type', 'delete']);

        $this->middleware(function (Request $request, $next) {
            /** @var User */
            $user = $request->user();
            /** @var Texture */
            $texture = $request->route('texture');

            if (!$texture->public) {
                if (!Auth::check() || ($user->uid != $texture->uploader && !$user->isAdmin())) {
                    $statusCode = (int) option('status_code_for_private');
                    if ($statusCode === 404) {
                        abort($statusCode, trans('skinlib.show.deleted'));
                    } else {
                        abort(403, trans('skinlib.show.private'));
                    }
                }
            }

            return $next($request);
        })->only(['show', 'info']);
    }

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
            ->when(
                $type === 'skin',
                fn (Builder $query) => $query->whereIn('type', ['steve', 'alex']),
                fn (Builder $query) => $query->where('type', $type),
            )
            ->when($keyword, fn (Builder $query, $keyword) => $query->like('name', $keyword))
            ->when($uploader, fn (Builder $query, $uploader) => $query->where('uploader', $uploader))
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

    public function show(Filter $filter, Texture $texture)
    {
        /** @var User */
        $user = Auth::user();
        /** @var FilesystemAdapter */
        $disk = Storage::disk('textures');

        if ($disk->missing($texture->hash)) {
            if (option('auto_del_invalid_texture')) {
                $texture->delete();
            }
            abort(404, trans('skinlib.show.deleted'));
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

        $converter = new GithubFlavoredMarkdownConverter();

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
                'contentPolicy' => $converter->convertToHtml(option_localized('content_policy')),
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

        /** @var UploadedFile */
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
            if ($ratio != 2 && $ratio != 1 || $type === 'alex' && $ratio === 2) {
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

        /** @var User */
        $user = Auth::user();

        $duplicated = Texture::where('hash', $hash)
            ->where(
                fn (Builder $query) => $query->where('public', true)->orWhere('uploader', $user->uid)
            )
            ->first();
        if ($duplicated) {
            // if the texture already uploaded was set to private,
            // then allow to re-upload it.
            return json(trans('skinlib.upload.repeated'), 2, ['tid' => $duplicated->tid]);
        }

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
            $file->storePubliclyAs('', $hash, ['disk' => 'textures']);
        }

        $user->score -= $cost;
        $user->closet()->attach($texture->tid, ['item_name' => $name]);
        $user->save();

        $dispatcher->dispatch('texture.uploaded', [$texture, $file]);

        return json(trans('skinlib.upload.success', ['name' => $name]), 0, [
            'tid' => $texture->tid,
        ]);
    }

    public function delete(Texture $texture, Dispatcher $dispatcher, Filter $filter)
    {
        $can = $filter->apply('can_delete_texture', true, [$texture]);
        if ($can instanceof Rejection) {
            return json($can->getReason(), 1);
        }

        $dispatcher->dispatch('texture.deleting', [$texture]);

        // check if file occupied
        if (Texture::where('hash', $texture->hash)->count() === 1) {
            Storage::disk('textures')->delete($texture->hash);
        }

        $texture->delete();

        $dispatcher->dispatch('texture.deleted', [$texture]);

        return json(trans('skinlib.delete.success'), 0);
    }

    public function privacy(Texture $texture, Dispatcher $dispatcher, Filter $filter)
    {
        $can = $filter->apply('can_update_texture_privacy', true, [$texture]);
        if ($can instanceof Rejection) {
            return json($can->getReason(), 1);
        }

        $uploader = $texture->owner;
        $score_diff = $texture->size
            * (option('private_score_per_storage') - option('score_per_storage'))
            * ($texture->public ? -1 : 1);
        if ($texture->public && option('take_back_scores_after_deletion', true)) {
            $score_diff -= option('score_award_per_texture', 0);
        }
        if ($uploader->score + $score_diff < 0) {
            return json(trans('skinlib.upload.lack-score'), 1);
        }

        if (!$texture->public) {
            $duplicated = Texture::where('hash', $texture->hash)
                ->where('public', true)
                ->first();
            if ($duplicated) {
                return json(trans('skinlib.upload.repeated'), 2, ['tid' => $duplicated->tid]);
            }
        }

        $dispatcher->dispatch('texture.privacy.updating', [$texture]);

        $uploader->score += $score_diff;
        $uploader->save();

        $texture->public = !$texture->public;
        $texture->save();

        $dispatcher->dispatch('texture.privacy.updated', [$texture]);

        $message = trans('skinlib.privacy.success', [
            'privacy' => (
                $texture->public
                    ? trans('general.public')
                    : trans('general.private')),
        ]);

        return json($message, 0);
    }

    public function rename(
        Request $request,
        Dispatcher $dispatcher,
        Filter $filter,
        Texture $texture
    ) {
        $data = $request->validate(['name' => [
            'required',
            option('texture_name_regexp')
                ? 'regex:'.option('texture_name_regexp')
                : 'string',
        ]]);
        $name = $data['name'];

        $can = $filter->apply('can_update_texture_name', true, [$texture, $name]);
        if ($can instanceof Rejection) {
            return json($can->getReason(), 1);
        }

        $dispatcher->dispatch('texture.name.updating', [$texture, $name]);

        $old = $texture->replicate();
        $texture->name = $name;
        $texture->save();

        $dispatcher->dispatch('texture.name.updated', [$texture, $old]);

        return json(trans('skinlib.rename.success', ['name' => $name]), 0);
    }

    public function type(
        Request $request,
        Dispatcher $dispatcher,
        Filter $filter,
        Texture $texture
    ) {
        $data = $request->validate([
            'type' => ['required', Rule::in(['steve', 'alex', 'cape'])],
        ]);
        $type = $data['type'];

        $can = $filter->apply('can_update_texture_type', true, [$texture, $type]);
        if ($can instanceof Rejection) {
            return json($can->getReason(), 1);
        }

        $dispatcher->dispatch('texture.type.updating', [$texture, $type]);

        $old = $texture->replicate();
        $texture->type = $type;
        $texture->save();

        $dispatcher->dispatch('texture.type.updated', [$texture, $old]);

        return json(trans('skinlib.model.success', ['model' => $type]), 0);
    }
}

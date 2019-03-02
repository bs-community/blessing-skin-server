<?php

namespace App\Http\Controllers;

use View;
use Option;
use Session;
use Storage;
use App\Models\User;
use App\Models\Closet;
use App\Models\Player;
use App\Models\Texture;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Services\Repositories\UserRepository;

class SkinlibController extends Controller
{
    /**
     * Map error code of file uploading to human-readable text.
     *
     * @see http://php.net/manual/en/features.file-upload.errors.php
     * @var array
     */
    public static $phpFileUploadErrors = [
        0 => 'There is no error, the file uploaded with success',
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3 => 'The uploaded file was only partially uploaded',
        4 => 'No file was uploaded',
        6 => 'Missing a temporary folder',
        7 => 'Failed to write file to disk.',
        8 => 'A PHP extension stopped the file upload.',
    ];

    public function index()
    {
        return view('skinlib.index', ['user' => Auth::user()]);
    }

    /**
     * Get skin library data filtered.
     * Available Query String: filter, uploader, page, sort, keyword, items_per_page.
     *
     * @param  Request $request [description]
     * @return JsonResponse
     */
    public function getSkinlibFiltered(Request $request)
    {
        $currentUser = Auth::user();

        // Available filters: skin, steve, alex, cape
        $filter = $request->input('filter', 'skin');

        // Filter result by uploader's uid
        $uploader = intval($request->input('uploader', 0));

        // Available sorting methods: time, likes
        $sort = $request->input('sort', 'time');
        $sortBy = ($sort == 'time') ? 'upload_at' : $sort;

        // Current page
        $page = $request->input('page', 1);
        $currentPage = ($page <= 0) ? 1 : $page;

        // How many items to show in one page
        $itemsPerPage = $request->input('items_per_page', 20);
        $itemsPerPage = $itemsPerPage <= 0 ? 20 : $itemsPerPage;

        // Keyword to search
        $keyword = $request->input('keyword', '');

        if ($filter == 'skin') {
            $query = Texture::where(function ($innerQuery) {
                // Nested condition, DO NOT MODIFY
                $innerQuery->where('type', '=', 'steve')->orWhere('type', '=', 'alex');
            });
        } else {
            $query = Texture::where('type', $filter);
        }

        if ($keyword !== '') {
            $query = $query->like('name', $keyword);
        }

        if ($uploader !== 0) {
            $query = $query->where('uploader', $uploader);
        }

        if (! $currentUser) {
            // Show public textures only to anonymous visitors
            $query = $query->where('public', true);
        } else {
            // Show private textures when show uploaded textures of current user
            if ($uploader != $currentUser->uid && ! $currentUser->isAdmin()) {
                $query = $query->where(function ($innerQuery) use ($currentUser) {
                    $innerQuery->where('public', true)->orWhere('uploader', '=', $currentUser->uid);
                });
            }
        }

        $totalPages = ceil($query->count() / $itemsPerPage);

        $textures = $query->orderBy($sortBy, 'desc')
                            ->skip(($currentPage - 1) * $itemsPerPage)
                            ->take($itemsPerPage)
                            ->get();

        if ($currentUser) {
            $closet = new Closet($currentUser->uid);
            foreach ($textures as $item) {
                $item->liked = $closet->has($item->tid);
            }
        }

        return response()->json([
            'items'       => $textures,
            'current_uid' => $currentUser ? $currentUser->uid : 0,
            'total_pages' => $totalPages,
        ]);
    }

    public function show($tid)
    {
        $texture = Texture::find($tid);
        $user = Auth::user();

        if (! $texture || $texture && ! Storage::disk('textures')->has($texture->hash)) {
            if (option('auto_del_invalid_texture')) {
                if ($texture) {
                    $texture->delete();
                }

                abort(404, trans('skinlib.show.deleted'));
            }
            abort(404, trans('skinlib.show.deleted').trans('skinlib.show.contact-admin'));
        }

        if (! $texture->public) {
            if (! Auth::check() || ($user->uid != $texture->uploader && ! $user->isAdmin())) {
                abort(403, trans('skinlib.show.private'));
            }
        }

        return view('skinlib.show')
            ->with('texture', $texture)
            ->with('with_out_filter', true)
            ->with('user', $user);
    }

    public function info($tid)
    {
        if ($t = Texture::find($tid)) {
            return json($t->toArray());
        } else {
            return json([]);
        }
    }

    public function upload()
    {
        return view('skinlib.upload')
            ->with('user', Auth::user())
            ->with('with_out_filter', true);
    }

    public function handleUpload(Request $request)
    {
        $user = Auth::user();

        if (($response = $this->checkUpload($request)) instanceof JsonResponse) {
            return $response;
        }

        $t = new Texture();
        $t->name = $request->input('name');
        $t->type = $request->input('type');
        $t->likes = 1;
        $t->hash = bs_hash_file($request->file('file'));
        $t->size = ceil($request->file('file')->getSize() / 1024);
        $t->public = $request->input('public') == 'true';
        $t->uploader = $user->uid;
        $t->upload_at = get_datetime_string();

        $cost = $t->size * ($t->public ? Option::get('score_per_storage') : Option::get('private_score_per_storage'));
        $cost += option('score_per_closet_item');

        if ($user->getScore() < $cost) {
            return json(trans('skinlib.upload.lack-score'), 7);
        }

        $results = Texture::where('hash', $t->hash)->get();

        if (! $results->isEmpty()) {
            foreach ($results as $result) {
                // if the texture already uploaded was set to private,
                // then allow to re-upload it.
                if ($result->type == $t->type && $result->public) {
                    return json(trans('skinlib.upload.repeated'), 0, [
                        'tid' => $result->tid,
                    ]);
                }
            }
        }

        if (! Storage::disk('textures')->exists($t->hash)) {
            Storage::disk('textures')->put($t->hash, file_get_contents($request->file('file')));
        }

        $t->save();

        $user->setScore($cost, 'minus');

        if ($user->getCloset()->add($t->tid, $t->name)) {
            return json(trans('skinlib.upload.success', ['name' => $request->input('name')]), 0, [
                'tid'   => $t->tid,
            ]);
        }
    }

    // @codeCoverageIgnore

    public function delete(Request $request, UserRepository $users)
    {
        $result = Texture::find($request->tid);
        $user = Auth::user();

        if (! $result) {
            return json(trans('skinlib.non-existent'), 1);
        }

        if ($result->uploader != $user->uid && ! $user->isAdmin()) {
            return json(trans('skinlib.no-permission'), 1);
        }

        // check if file occupied
        if (Texture::where('hash', $result->hash)->count() == 1) {
            Storage::disk('textures')->delete($result->hash);
        }

        if (option('return_score')) {
            if ($u = $users->get($result->uploader)) {
                if ($result->public) {
                    $u->setScore(
                        $result->size * option('score_per_storage'), 'plus'
                    );
                } else {
                    $u->setScore(
                        $result->size * option('private_score_per_storage'), 'plus'
                    );
                }
            }
        }

        if ($result->delete()) {
            return json(trans('skinlib.delete.success'), 0);
        }
    }

    // @codeCoverageIgnore

    public function privacy(Request $request, UserRepository $users)
    {
        $t = Texture::find($request->input('tid'));
        $user = Auth::user();

        if (! $t) {
            return json(trans('skinlib.non-existent'), 1);
        }

        if ($t->uploader != $user->uid && ! $user->isAdmin()) {
            return json(trans('skinlib.no-permission'), 1);
        }

        $score_diff = $t->size * (option('private_score_per_storage') - option('score_per_storage')) * ($t->public ? -1 : 1);
        if ($users->get($t->uploader)->getScore() + $score_diff < 0) {
            return json(trans('skinlib.upload.lack-score'), 1);
        }

        $type = $t->type == 'cape' ? 'cape' : 'skin';
        Player::where("tid_$type", $t->tid)
            ->where('uid', '<>', session('uid'))
            ->get()
            ->each(function ($player) use ($type) {
                $player->setTexture(["tid_$type" => 0]);
            });

        @$users->get($t->uploader)->setScore($score_diff, 'plus');

        $t->public = ! $t->public;
        $t->save();

        return json([
            'errno'  => 0,
            'msg'    => trans('skinlib.privacy.success', ['privacy' => (! $t->public ? trans('general.private') : trans('general.public'))]),
            'public' => $t->public,
        ]);
    }

    // @codeCoverageIgnore

    public function rename(Request $request)
    {
        $this->validate($request, [
            'tid'      => 'required|integer',
            'new_name' => 'required|no_special_chars',
        ]);
        $user = Auth::user();
        $t = Texture::find($request->input('tid'));

        if (! $t) {
            return json(trans('skinlib.non-existent'), 1);
        }

        if ($t->uploader != $user->uid && ! $user->isAdmin()) {
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
        $user = Auth::user();
        $data = $this->validate($request, [
            'tid'      => 'required|integer',
            'model'    => 'required|in:steve,alex,cape',
        ]);

        $t = Texture::find($request->input('tid'));

        if (! $t) {
            return json(trans('skinlib.non-existent'), 1);
        }

        if ($t->uploader != $user->uid && ! $user->isAdmin()) {
            return json(trans('skinlib.no-permission'), 1);
        }

        $duplicate = Texture::where('hash', $t->hash)
            ->where('type', $request->input('model'))
            ->where('tid', '<>', $t->tid)
            ->first();
        if ($duplicate && $duplicate->public) {
            return json(trans('skinlib.model.duplicate', ['name' => $duplicate->name]), 1);
        }

        $t->type = $request->input('model');
        $t->save();

        return json(trans('skinlib.model.success', ['model' => $data['model']]), 0);
    }

    /**
     * Check Uploaded Files.
     *
     * @param  Request $request
     * @return JsonResponse
     */
    protected function checkUpload(Request $request)
    {
        if ($file = $request->files->get('file')) {
            if ($file->getError() !== UPLOAD_ERR_OK) {
                return json(static::$phpFileUploadErrors[$file->getError()], $file->getError());
            }
        }

        $this->validate($request, [
            'name'   => [
                'required',
                option('texture_name_regexp') ? 'regex:'.option('texture_name_regexp') : 'no_special_chars',
            ],
            'file'   => 'required|max:'.option('max_upload_file_size'),
            'public' => 'required',
        ]);

        $mime = $request->file('file')->getMimeType();
        if ($mime != 'image/png' && $mime != 'image/x-png') {
            return json(trans('skinlib.upload.type-error'), 1);
        }

        $type = $request->input('type');
        $size = getimagesize($request->file('file'));
        $ratio = $size[0] / $size[1];

        if ($type == 'steve' || $type == 'alex') {
            if ($ratio != 2 && $ratio != 1) {
                return json(trans('skinlib.upload.invalid-size', ['type' => trans('general.skin'), 'width' => $size[0], 'height' => $size[1]]), 1);
            }
            if ($size[0] % 64 != 0 || $size[1] % 32 != 0) {
                return json(trans('skinlib.upload.invalid-hd-skin', ['type' => trans('general.skin'), 'width' => $size[0], 'height' => $size[1]]), 1);
            }
        } elseif ($type == 'cape') {
            if ($ratio != 2) {
                return json(trans('skinlib.upload.invalid-size', ['type' => trans('general.cape'), 'width' => $size[0], 'height' => $size[1]]), 1);
            }
        } else {
            return json(trans('general.illegal-parameters'), 1);
        }
    }

    // @codeCoverageIgnore
}

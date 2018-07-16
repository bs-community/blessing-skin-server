<?php

namespace App\Http\Controllers;

use View;
use Utils;
use Option;
use Storage;
use Session;
use App\Models\User;
use App\Models\Closet;
use App\Models\Player;
use App\Models\Texture;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Exceptions\PrettyPageException;
use App\Services\Repositories\UserRepository;

class SkinlibController extends Controller
{
    protected $user = null;

    public function __construct(UserRepository $users)
    {
        $this->middleware(function ($request, $next) use ($users) {
            $this->user = $users->get($request->session()->get('uid'));
            return $next($request);
        });
    }

    public function index()
    {
        return view('skinlib.index', ['user' => $this->user]);
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

        // Available filters: skin, steve, alex, cape
        $filter = $request->input('filter', 'skin');

        // Filter result by uploader's uid
        $uploader = intval($request->input('uploader', 0));

        // Available sorting methods: time, likes
        $sort = $request->input('sort', 'time');
        $sortBy = ($sort == "time") ? "upload_at" : $sort;

        // Current page
        $page = $request->input('page', 1);
        $currentPage = ($page <= 0) ? 1 : $page;

        // How many items to show in one page
        $itemsPerPage = $request->input('items_per_page', 20);
        $itemsPerPage = $itemsPerPage <= 0 ? 20 : $itemsPerPage;

        // Keyword to search
        $keyword = $request->input('keyword', '');

        // Check if user logged in
        $anonymous = is_null($this->user);

        if ($filter == "skin") {
            $query = Texture::where(function ($innerQuery) {
                // Nested condition, DO NOT MODIFY
                $innerQuery->where('type', '=', 'steve')->orWhere('type', '=', 'alex');
            });
        } else {
            $query = Texture::where('type', $filter);
        }

        if ($keyword !== "") {
            $query = $query->like('name', $keyword);
        }

        if ($uploader !== 0) {
            $query = $query->where('uploader', $uploader);
        }

        if ($anonymous) {
            // Show public textures only to anonymous visitors
            $query = $query->where('public', true);
        } else {
            // Show private textures when show uploaded textures of current user
            if ($uploader != $this->user->uid && !$this->user->isAdmin()) {
                $query = $query->where(function ($innerQuery) {
                    $innerQuery->where('public', true)->orWhere('uploader', '=', $this->user->uid);
                });
            }
        }

        $totalPages = ceil($query->count() / $itemsPerPage);

        $textures = $query->orderBy($sortBy, 'desc')
                            ->skip(($currentPage - 1) * $itemsPerPage)
                            ->take($itemsPerPage)
                            ->get();

        if (! $anonymous) {
            $closet = new Closet($this->user->uid);
            foreach ($textures as $item) {
                $item->liked = $closet->has($item->tid);
            }
        }

        return response()->json([
            'items'       => $textures,
            'anonymous'   => $anonymous,
            'total_pages' => $totalPages
        ]);
    }

    public function show($tid)
    {
        $texture = Texture::find($tid);

        if (! $texture || $texture && !Storage::disk('textures')->has($texture->hash)) {
            if (option('auto_del_invalid_texture')) {
                if ($texture) {
                    $texture->delete();
                }

                abort(404, trans('skinlib.show.deleted'));
            }
            abort(404, trans('skinlib.show.deleted').trans('skinlib.show.contact-admin'));
        }

        if (!$texture->public) {
            if (is_null($this->user) || ($this->user->uid != $texture->uploader && !$this->user->isAdmin()))
                abort(403, trans('skinlib.show.private'));
        }

        return view('skinlib.show')->with('texture', $texture)->with('with_out_filter', true)->with('user', $this->user);
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
        return view('skinlib.upload')->with('user', $this->user)->with('with_out_filter', true);
    }

    public function handleUpload(Request $request)
    {
        // Hacking for testing
        if (config('app.env') == 'testing') {
            $this->user = User::find($this->user->uid);
        }

        if (($response = $this->checkUpload($request)) instanceof JsonResponse) {
            return $response;
        }

        $t            = new Texture();
        $t->name      = $request->input('name');
        $t->type      = $request->input('type');
        $t->likes     = 1;
        $t->hash      = bs_hash_file($request->file('file'));
        $t->size      = ceil($request->file('file')->getSize() / 1024);
        $t->public    = $request->input('public') == 'true';
        $t->uploader  = $this->user->uid;
        $t->upload_at = Utils::getTimeFormatted();

        $cost = $t->size * ($t->public ? Option::get('score_per_storage') : Option::get('private_score_per_storage'));
        $cost += option('score_per_closet_item');

        if ($this->user->getScore() < $cost)
            return json(trans('skinlib.upload.lack-score'), 7);

        $results = Texture::where('hash', $t->hash)->get();

        if (! $results->isEmpty()) {
            foreach ($results as $result) {
                // if the texture already uploaded was set to private,
                // then allow to re-upload it.
                if ($result->type == $t->type && $result->public) {
                    return json(trans('skinlib.upload.repeated'), 0, [
                        'tid' => $result->tid
                    ]);
                }
            }
        }

        if (! Storage::disk('textures')->exists($t->hash)) {
            Storage::disk('textures')->put($t->hash, file_get_contents($request->file('file')));
        }

        $t->save();

        $this->user->setScore($cost, 'minus');

        if ($this->user->getCloset()->add($t->tid, $t->name)) {
            return json(trans('skinlib.upload.success', ['name' => $request->input('name')]), 0, [
                'tid'   => $t->tid
            ]);
        }
    }   // @codeCoverageIgnore

    public function delete(Request $request, UserRepository $users)
    {
        $result = Texture::find($request->tid);

        if (! $result) {
            return json(trans('skinlib.non-existent'), 1);
        }

        if ($result->uploader != $this->user->uid && !$this->user->isAdmin()) {
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
    }   // @codeCoverageIgnore

    public function privacy(Request $request, UserRepository $users)
    {
        $t = Texture::find($request->input('tid'));

        if (! $t)
            return json(trans('skinlib.non-existent'), 1);

        if ($t->uploader != $this->user->uid && !$this->user->isAdmin())
            return json(trans('skinlib.no-permission'), 1);

        $score_diff = $t->size * (option('private_score_per_storage') - option('score_per_storage')) * ($t->public ? -1 : 1);
        if ($users->get($t->uploader)->getScore() + $score_diff < 0) {
            return json(trans('skinlib.upload.lack-score'), 1);
        }

        $type = $t->type;
        Player::where("tid_$type", $t->tid)
            ->where('uid', '<>', session('uid'))
            ->get()
            ->each(function ($player) use ($type) {
                $player->setTexture(["tid_$type" => 0]);
            });

        @$users->get($t->uploader)->setScore($score_diff, 'plus');

        if ($t->setPrivacy(!$t->public)) {
            return json([
                'errno'  => 0,
                'msg'    => trans('skinlib.privacy.success', ['privacy' => (!$t->public ? trans('general.private') : trans('general.public'))]),
                'public' => $t->public
            ]);
        }
    }   // @codeCoverageIgnore

    public function rename(Request $request) {
        $this->validate($request, [
            'tid'      => 'required|integer',
            'new_name' => 'required|no_special_chars'
        ]);

        $t = Texture::find($request->input('tid'));

        if (! $t)
            return json(trans('skinlib.non-existent'), 1);

        if ($t->uploader != $this->user->uid && !$this->user->isAdmin())
            return json(trans('skinlib.no-permission'), 1);

        $t->name = $request->input('new_name');

        if ($t->save()) {
            return json(trans('skinlib.rename.success', ['name' => $request->input('new_name')]), 0);
        }
    }   // @codeCoverageIgnore

    /**
     * Check Uploaded Files
     *
     * @param  Request $request
     * @return JsonResponse
     */
    protected function checkUpload(Request $request)
    {
        if ($file = $request->files->get('file')) {
            if ($file->getError() !== UPLOAD_ERR_OK) {
                return json(Utils::convertUploadFileError($file->getError()), $file->getError());
            }
        }

        $this->validate($request, [
            'name'   => 'required|no_special_chars',
            'file'   => 'required|max:'.option('max_upload_file_size'),
            'public' => 'required'
        ]);

        if (extension_loaded('fileinfo')) {
            $mime = $request->file('file')->getMimeType();
        } else {
            $mime = $_FILES['file']['type'];
        }

        if ($mime != "image/png" && $mime != "image/x-png") {
            return json(trans('skinlib.upload.type-error'), 1);
        }

        $type  = $request->input('type');
        $size  = getimagesize($request->file('file'));
        $ratio = $size[0] / $size[1];

        if ($type == "steve" || $type == "alex") {
            if ($ratio != 2 && $ratio != 1)
                return json(trans('skinlib.upload.invalid-size', ['type' => trans('general.skin'), 'width' => $size[0], 'height' => $size[1]]), 1);
            if ($size[0] % 64 != 0 || $size[1] % 32 != 0)
                return json(trans('skinlib.upload.invalid-hd-skin', ['type' => trans('general.skin'), 'width' => $size[0], 'height' => $size[1]]), 1);
        } elseif ($type == "cape") {
            if ($ratio != 2)
                return json(trans('skinlib.upload.invalid-size', ['type' => trans('general.cape'), 'width' => $size[0], 'height' => $size[1]]), 1);
        } else {
            return json(trans('general.illegal-parameters'), 1);
        }
    }       // @codeCoverageIgnore

}

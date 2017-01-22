<?php

namespace App\Http\Controllers;

use View;
use Utils;
use Option;
use Storage;
use Session;
use App\Models\User;
use App\Models\Texture;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Exceptions\PrettyPageException;
use App\Services\Repositories\UserRepository;

class SkinlibController extends Controller
{
    private $user = null;

    public function __construct(UserRepository $users)
    {
        // Try to load user by uid stored in session.
        // If there is no uid stored in session or the uid is invalid
        // it will return a null value.
        $this->user = $users->get(session('uid'));
    }

    public function index(Request $request)
    {
        $filter  = $request->input('filter', 'skin');
        $sort    = $request->input('sort', 'time');
        $uid     = $request->input('uid', 0);
        $page    = $request->input('page', 1);
        $page    = $page <= 0 ? 1 : $page;

        $sort_by = ($sort == "time") ? "upload_at" : $sort;

        if ($filter == "skin") {
            $textures = Texture::where(function($query) {
                $query->where('type',   '=', 'steve')
                      ->orWhere('type', '=', 'alex');
            })->orderBy($sort_by, 'desc');

        } elseif ($filter == "user") {
            $textures = Texture::where('uploader', $uid)->orderBy($sort_by, 'desc');

        } else {
            $textures = Texture::where('type', $filter)->orderBy($sort_by, 'desc');
        }

        if (!is_null($this->user)) {
            // show private textures when show uploaded textures of current user
            if ($uid != $this->user->uid && !$this->user->isAdmin())
                $textures = $textures->where('public', '1');
        } else {
            $textures = $textures->where('public', '1');
        }

        $total_pages = ceil($textures->count() / 20);

        $textures = $textures->skip(($page - 1) * 20)->take(20)->get();

        return view('skinlib.index')->with('user', $this->user)
                                    ->with('sort', $sort)
                                    ->with('filter', $filter)
                                    ->with('textures', $textures)
                                    ->with('page', $page)
                                    ->with('total_pages', $total_pages);
    }

    public function search(Request $request)
    {
        $q      = $request->input('q', '');
        $filter = $request->input('filter', 'skin');
        $sort   = $request->input('sort', 'time');

        $sort_by = ($sort == "time") ? "upload_at" : $sort;

        if ($filter == "skin") {
            $textures = Texture::like('name', $q)->where(function($query) use ($q) {
                $query->where('public', '=', '1')
                      ->where('type',   '=', 'steve')
                      ->orWhere('type', '=', 'alex');
            })->orderBy($sort_by, 'desc')->get();
        } else {
            $textures = Texture::like('name', $q)
                                ->where('type', $filter)
                                ->where('public', '1')
                                ->orderBy($sort_by, 'desc')->get();
        }

        return view('skinlib.search')->with('user', $this->user)
                                    ->with('sort', $sort)
                                    ->with('filter', $filter)
                                    ->with('q', $q)
                                    ->with('textures', $textures);
    }

    public function show($tid)
    {
        $texture = Texture::find($tid);

        if (!$texture || $texture && !Storage::disk('textures')->has($texture->hash)) {
            if (Option::get('auto_del_invalid_texture') == "1") {
                if ($texture)
                    $texture->delete();

                abort(404, trans('skinlib.show.deleted'));
            }
            abort(404, trans('skinlib.show.deleted').trans('skinlib.show.contact-admin'));
        }

        if ($texture->public == "0") {
            if (is_null($this->user) || ($this->user->uid != $texture->uploader && !$this->user->isAdmin()))
                abort(404, trans('skinlib.show.private'));
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
        if (($response = $this->checkUpload($request)) instanceof JsonResponse) {
            return $response;
        }

        $t            = new Texture();
        $t->name      = $request->input('name');
        $t->type      = $request->input('type');
        $t->likes     = 1;
        $t->hash      = Utils::upload($_FILES['file']);
        $t->size      = ceil($_FILES['file']['size'] / 1024);
        $t->public    = ($request->input('public') == 'true') ? "1" : "0";
        $t->uploader  = $this->user->uid;
        $t->upload_at = Utils::getTimeFormatted();

        $cost = $t->size * (($t->public == "1") ? Option::get('score_per_storage') : Option::get('private_score_per_storage'));

        if ($this->user->getScore() < $cost)
            return json(trans('skinlib.upload.lack-score'), 7);

        $results = Texture::where('hash', $t->hash)->get();

        if (!$results->isEmpty()) {
            foreach ($results as $result) {
                // if the texture already uploaded was setted to private,
                // then allow to re-upload it.
                if ($result->type == $t->type && $result->public == "1") {
                    return json(trans('skinlib.upload.repeated'), 0, [
                        'tid' => $result->tid
                    ]);
                }
            }
        }

        $t->save();

        $this->user->setScore($cost, 'minus');

        if ($this->user->getCloset()->add($t->tid, $t->name)) {
            return json(trans('skinlib.upload.success', ['name' => $request->input('name')]), 0, [
                'tid'   => $t->tid
            ]);
        }
    }

    public function delete(Request $request)
    {
        $result = Texture::find($request->tid);

        if (!$result)
            return json(trans('skinlib.non-existent'), 1);

        if ($result->uploader != $this->user->uid && !$this->user->isAdmin())
            return json(trans('skinlib.no-permission'), 1);

        // check if file occupied
        if (Texture::where('hash', $result['hash'])->count() == 1)
            Storage::delete($result['hash']);

        if (option('return_score')) {
            $this->user->setScore($result->size * Option::get('score_per_storage'), 'plus');
        }

        if ($result->delete())
            return json(trans('skinlib.delete.success'), 0);
    }

    public function privacy(Request $request)
    {
        $t = Texture::find($request->input('tid'));

        if (!$t)
            return json(trans('skinlib.non-existent'), 1);

        if ($t->uploader != $this->user->uid && !$this->user->isAdmin())
            return json(trans('skinlib.no-permission'), 1);

        if ($t->setPrivacy(!$t->public)) {
            return json([
                'errno'  => 0,
                'msg'    => trans('skinlib.privacy.success', ['privacy' => ($t->public == "0" ? trans('general.private') : trans('general.public'))]),
                'public' => $t->public
            ]);
        }
    }

    public function rename(Request $request) {
        $this->validate($request, [
            'tid'      => 'required|integer',
            'new_name' => 'required|no_special_chars'
        ]);

        $t = Texture::find($request->input('tid'));

        if (!$t)
            return json(trans('skinlib.non-existent'), 1);

        if ($t->uploader != $this->user->uid && !$this->user->isAdmin())
            return json(trans('skinlib.no-permission'), 1);

        $t->name = $request->input('new_name');

        if ($t->save()) {
            return json(trans('skinlib.rename.success', ['name' => $request->input('new_name')]), 0);
        }
    }

    /**
     * Check Uploaded Files
     *
     * @param  Request $request
     * @return void
     */
    private function checkUpload(Request $request)
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

        if ($_FILES['file']['type'] != "image/png" && $_FILES['file']['type'] != "image/x-png") {
            return json(trans('skinlib.upload.type-error'), 1);
        }

        // if error occured while uploading file
        if ($_FILES['file']["error"] > 0)
            return json($_FILES['file']["error"], 1);

        $type  = $request->input('type');
        $size  = getimagesize($_FILES['file']["tmp_name"]);
        $ratio = $size[0] / $size[1];

        if ($type == "steve" || $type == "alex") {
            if ($ratio != 2 && $ratio != 1)
                return json(trans('skinlib.upload.invalid-size', ['type' => trans('general.skin'), 'width' => $size[0], 'height' => $size[1]]), 1);
        } elseif ($type == "cape") {
            if ($ratio != 2)
                return json(trans('skinlib.upload.invalid-size', ['type' => trans('general.cape'), 'width' => $size[0], 'height' => $size[1]]), 1);
        } else {
            return json(trans('general.illegal-parameters'), 1);
        }
    }

}

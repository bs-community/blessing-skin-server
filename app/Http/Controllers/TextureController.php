<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Texture;
use App\Models\User;
use Blessing\Minecraft;
use Cache;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Image;
use Storage;

class TextureController extends Controller
{
    public function __construct()
    {
        $this->middleware('cache.headers:public;max_age='.option('cache_expire_time'))
            ->only(['json']);

        $this->middleware('cache.headers:etag;public;max_age='.option('cache_expire_time'))
            ->only([
                'preview',
                'raw',
                'texture',
                'avatarByPlayer',
                'avatarByUser',
                'avatarByTexture',
            ]);
    }

    public function json($player)
    {
        $player = Player::where('name', $player)->firstOrFail();
        $isBanned = $player->user->permission === User::BANNED;
        abort_if($isBanned, 403, trans('general.player-banned'));

        return response()->json($player)->setLastModified($player->last_modified);
    }

    public function previewByHash(Minecraft $minecraft, Request $request, $hash)
    {
        $texture = Texture::where('hash', $hash)->firstOrFail();

        return $this->preview($minecraft, $request, $texture);
    }

    public function preview(Minecraft $minecraft, Request $request, Texture $texture)
    {
        $tid = $texture->tid;
        $hash = $texture->hash;
        $usePNG = $request->has('png') || !(imagetypes() & IMG_WEBP);
        $format = $usePNG ? 'png' : 'webp';

        $disk = Storage::disk('textures');
        abort_if($disk->missing($hash), 404);

        $height = (int) $request->query('height', 200);
        $now = Carbon::now();
        $response = Cache::remember(
            'preview-t'.$tid."-$format",
            option('enable_preview_cache') ? $now->addYear() : $now->addMinute(),
            function () use ($minecraft, $disk, $texture, $hash, $height, $usePNG) {
                $file = $disk->get($hash);
                if ($texture->type === 'cape') {
                    $image = $minecraft->renderCape($file, $height);
                } else {
                    $image = $minecraft->renderSkin($file, 12, $texture->type === 'alex');
                }

                $lastModified = $disk->lastModified($hash);

                return Image::make($image)
                    ->response($usePNG ? 'png' : 'webp', 100)
                    ->setLastModified(Carbon::createFromTimestamp($lastModified));
            }
        );

        return $response;
    }

    public function raw($tid)
    {
        abort_unless(option('allow_downloading_texture'), 403);

        $texture = Texture::findOrFail($tid);

        return $this->texture($texture->hash);
    }

    public function texture(string $hash)
    {
        $disk = Storage::disk('textures');
        abort_if($disk->missing($hash), 404);

        $lastModified = Carbon::createFromTimestamp($disk->lastModified($hash));

        return response($disk->get($hash))
            ->withHeaders([
                'Content-Type' => 'image/png',
                'Content-Length' => $disk->size($hash),
            ])
            ->setLastModified($lastModified);
    }

    public function avatarByPlayer(Minecraft $minecraft, Request $request, $name)
    {
        $player = Player::where('name', $name)->firstOrFail();

        return $this->avatar($minecraft, $request, $player->skin);
    }

    public function avatarByUser(Minecraft $minecraft, Request $request, $uid)
    {
        $texture = Texture::find(optional(User::find($uid))->avatar);

        return $this->avatar($minecraft, $request, $texture);
    }

    public function avatarByHash(Minecraft $minecraft, Request $request, $hash)
    {
        $texture = Texture::where('hash', $hash)->first();

        return $this->avatar($minecraft, $request, $texture);
    }

    public function avatarByTexture(Minecraft $minecraft, Request $request, $tid)
    {
        $texture = Texture::find($tid);

        return $this->avatar($minecraft, $request, $texture);
    }

    protected function avatar(Minecraft $minecraft, Request $request, ?Texture $texture)
    {
        if (!empty($texture) && $texture->type !== 'steve' && $texture->type !== 'alex') {
            return abort(422);
        }

        $size = (int) $request->query('size', 100);
        $mode = $request->has('3d') ? '3d' : '2d';
        $usePNG = $request->has('png') || !(imagetypes() & IMG_WEBP);
        $format = $usePNG ? 'png' : 'webp';

        $disk = Storage::disk('textures');
        if (is_null($texture) || $disk->missing($texture->hash)) {
            return Image::make(resource_path("misc/textures/avatar$mode.png"))
                ->resize($size, $size)
                ->response($usePNG ? 'png' : 'webp', 100);
        }

        $hash = $texture->hash;
        $now = Carbon::now();
        $response = Cache::remember(
            'avatar-'.$mode.'-t'.$texture->tid.'-s'.$size."-$format",
            option('enable_avatar_cache') ? $now->addYear() : $now->addMinute(),
            function () use ($minecraft, $disk, $hash, $size, $mode, $usePNG) {
                $file = $disk->get($hash);
                if ($mode === '3d') {
                    $image = $minecraft->render3dAvatar($file, 25);
                } else {
                    $image = $minecraft->render2dAvatar($file, 25);
                }

                $lastModified = Carbon::createFromTimestamp($disk->lastModified($hash));

                return Image::make($image)
                    ->resize($size, $size)
                    ->response($usePNG ? 'png' : 'webp', 100)
                    ->setLastModified($lastModified);
            }
        );

        return $response;
    }
}

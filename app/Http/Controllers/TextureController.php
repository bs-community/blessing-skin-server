<?php

namespace App\Http\Controllers;

use Event;
use Option;
use Storage;
use Response;
use Minecraft;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Player;
use App\Models\Texture;
use Illuminate\Http\Request;
use App\Events\GetSkinPreview;
use App\Events\GetAvatarPreview;
use App\Exceptions\PrettyPageException;
use App\Services\Repositories\UserRepository;

class TextureController extends Controller
{
    /**
     * Return Player Profile formatted in JSON.
     *
     * @param  string $player_name
     * @param  string $api
     * @return \Illuminate\Http\Response
     */
    public function json($player_name, $api = "")
    {
        $player = $this->getPlayerInstance($player_name);

        if ($api == "csl") {
            $content = $player->getJsonProfile(Player::CSL_API);
        } else if ($api == "usm") {
            $content = $player->getJsonProfile(Player::USM_API);
        } else {
            $content = $player->getJsonProfile(Option::get('api_type'));
        }

        return Response::rawJson($content, 200, [
            'Last-Modified' => Carbon::createFromTimestamp($player->getLastModified())->format('D, d M Y H:i:s \G\M\T')
        ]);
    }

    public function jsonWithApi($api, $player_name)
    {
        return $this->json($player_name, $api);
    }

    public function texture($hash) {
        if (Storage::disk('textures')->has($hash)) {
            return Response::png(Storage::disk('textures')->get($hash), 200, [
                'Last-Modified'  => Storage::disk('textures')->lastModified($hash),
                'Accept-Ranges'  => 'bytes',
                'Content-Length' => Storage::disk('textures')->size($hash),
            ]);
        } else {
            abort(404);
        }
    }

    public function textureWithApi($api, $hash) {
        return $this->texture($hash);
    }

    public function skin($player_name, $model = "")
    {
        $player = $this->getPlayerInstance($player_name);

        $model_preference = ($player->getPreference() == "default") ? "steve" : "alex";
        $model = ($model == "") ? $model_preference : $model;

        return $player->getBinaryTexture($model);
    }

    public function skinWithModel($model, $player_name)
    {
        return $this->skin($player_name, $model);
    }

    public function cape($player_name)
    {
        $player = $this->getPlayerInstance($player_name);

        return $player->getBinaryTexture('cape');
    }

    public function avatar($base64_email, $size = 128, UserRepository $users)
    {
        $user = $users->get(base64_decode($base64_email), 'email');

        if ($user) {
            $tid = $user->getAvatarId();

            if ($t = Texture::find($tid)) {
                if (Storage::disk('textures')->has($t->hash)) {
                    $responses = Event::fire(new GetAvatarPreview($t, $size));

                    if (isset($responses[0]) && $responses[0] instanceof \Symfony\Component\HttpFoundation\Response) {
                        return $responses[0];
                    } else {
                        $filename = BASE_DIR."/storage/textures/{$t->hash}";

                        $png = Minecraft::generateAvatarFromSkin($filename, $size);
                        imagepng($png);
                        imagedestroy($png);

                        return Response::png();
                    }
                }
            }
        }

        $png = imagecreatefromstring(base64_decode("iVBORw0KGgoAAAANSUhEUgAAAIAAAACACAIAAABMXPacAAACSElEQVR4nO3csUpbcQBGca/epBDTFio0KCLiC/QNRJBCod10LkUwfQq7uYhDKa5Ondri4NLRoX2GLl1EVGqhQcVEiYnSZ/jAy8lwfvN3r5LDf7lXU7yYfzqWGNzdR/vaxHil9x8Oh9E+VZZlpffPPh09OAPADAAzAMwAMAPADAAzAMwAMAPADAAzAMwAMAPADAAr0+fvqarfH0w1G9E+1eneVnp/TwDMADADwAwAMwDMADADwAwAMwDMADADwAwAMwDMADADwOI/ft9afxftG416+iNGyr/Lq2i/+flLtPcEwAwAMwDMADADwAwAMwDMADADwAwAMwDMADADwAwAMwCs+LrRji5o1B5F++tBv9L7n12cR/uFmVa0P/z9K9qnPAEwA8AMADMAzAAwA8AMADMAzAAwA8AMADMAzAAwA8AMACvT5++vNz5F+5XFrWi/9vIk2qfP93f2n0f7vR/b0f7bh/fR3hMAMwDMADADwAwAMwDMADADwAwAMwDMADADwAwAMwDMALBit/0quuBxay7ap+8bvh/8jPap1TfL0T79/4A/nU609wTADAAzAMwAMAPADAAzAMwAMAPADAAzAMwAMAPADAAzAKz4+HYpuqBeFNH+6Lgb7UfN9OxktO/2etHeEwAzAMwAMAPADAAzAMwAMAPADAAzAMwAMAPADAAzAMwAsPL0b/b9+71+9j5gZqoW7VOX3dto/+xJM9rf3Ayiffr7eAJgBoAZAGYAmAFgBoAZAGYAmAFgBoAZAGYAmAFgBoAZAPYfTCpLwD1OEBAAAAAASUVORK5CYII="));
        imagepng($png);
        imagedestroy($png);

        return Response::png();
    }

    public function avatarWithSize($size, $base64_email, UserRepository $users)
    {
        return $this->avatar($base64_email, $size, $users);
    }

    public function preview($tid, $size = 250)
    {
        // output image directly

        if ($t = Texture::find($tid)) {
            if (Storage::disk('textures')->has($t->hash)) {
                $responses = Event::fire(new GetSkinPreview($t, $size));

                if (isset($responses[0]) && $responses[0] instanceof \Symfony\Component\HttpFoundation\Response) {
                    return $responses[0];
                } else {
                    $filename = BASE_DIR."/storage/textures/{$t->hash}";

                    if ($t->type == "cape") {
                        $png = Minecraft::generatePreviewFromCape($filename, $size);
                        imagepng($png);
                        imagedestroy($png);
                    } else {
                        $png = Minecraft::generatePreviewFromSkin($filename, $size);
                        imagepng($png);
                        imagedestroy($png);
                    }

                    return Response::png();
                }
            }
        }

        $png = imagecreatefromstring(base64_decode("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"));
        imagepng($png);
        imagedestroy($png);

        return Response::png();
    }

    public function previewWithSize($size, $tid)
    {
        return $this->preview($tid, $size);
    }

    public function raw($tid) {
        if ($t = Texture::find($tid)) {

            if (Storage::disk('textures')->has($t->hash)) {
                return Response::png(Storage::disk('textures')->get($t->hash));
            } else {
                abort(404, '请求的材质文件已经被删除');
            }
        } else {
            abort(404, '材质不存在');
        }

    }

    private function getPlayerInstance($player_name)
    {
        $player = Player::where('player_name', $player_name)->first();

        if (!$player)
            abort(404, '角色不存在');

        if ($player->isBanned())
            abort(404, '该角色拥有者已被本站封禁。');

        return $player;
    }

}

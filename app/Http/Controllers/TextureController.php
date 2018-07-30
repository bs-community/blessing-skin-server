<?php

namespace App\Http\Controllers;

use Event;
use Option;
use Storage;
use Response;
use Minecraft;
use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Player;
use App\Models\Texture;
use Illuminate\Http\Request;
use App\Events\GetSkinPreview;
use App\Events\GetAvatarPreview;
use App\Exceptions\PrettyPageException;
use App\Services\Repositories\UserRepository;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

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
            'Last-Modified' => Carbon::createFromTimestamp(
                $player->getLastModified()
            )->format('D, d M Y H:i:s \G\M\T')
        ]);
    }

    public function jsonWithApi($api, $player_name)
    {
        return $this->json($player_name, $api);
    }

    public function texture($hash) {
        try {
            if (Storage::disk('textures')->has($hash)) {
                return Response::png(Storage::disk('textures')->get($hash), 200, [
                    'Last-Modified'  => Storage::disk('textures')->lastModified($hash),
                    'Accept-Ranges'  => 'bytes',
                    'Content-Length' => Storage::disk('textures')->size($hash),
                ]);
            }
        } catch (Exception $e) {
            report($e);
        }

        return abort(404);
    }

    public function textureWithApi($api, $hash) {
        return $this->texture($hash);
    }

    public function skin($player_name, $model = "")
    {
        $player = $this->getPlayerInstance($player_name);

        $model_preference = ($player->getPreference() == "default") ? "steve" : "alex";
        $model = ($model == "") ? $model_preference : $model;

        return $this->getBinaryTextureFromPlayer($player_name, $model);
    }

    public function skinWithModel($model, $player_name)
    {
        return $this->skin($player_name, $model);
    }

    public function cape($player_name)
    {
        return $this->getBinaryTextureFromPlayer($player_name, 'cape');
    }

    /**
     * Get the texture image of given type and player.
     *
     * @param  string $player_name
     * @param  string $type "steve" or "alex" or "cape".
     * @return void|Response
     */
    protected function getBinaryTextureFromPlayer($player_name, $type)
    {
        $player = $this->getPlayerInstance($player_name);

        if ($hash = $player->getTexture($type)) {
            if (Storage::disk('textures')->has($hash)) {
                // Cache friendly
                return Response::png(Storage::disk('textures')->read($hash), 200, [
                    'Last-Modified'  => $player->getLastModified(),
                    'Accept-Ranges'  => 'bytes',
                    'Content-Length' => Storage::disk('textures')->size($hash),
                ]);
            } else {
                abort(404, trans('general.texture-deleted'));
            }
        } else {
            abort(404, trans('general.texture-not-uploaded', ['type' => $type]));
        }
    }  // @codeCoverageIgnore

    public function avatarByTid($tid, $size = 128)
    {
        if ($t = Texture::find($tid)) {
            try {
                if (Storage::disk('textures')->has($t->hash)) {
                    $responses = event(new GetAvatarPreview($t, $size));

                    if (isset($responses[0]) && $responses[0] instanceof SymfonyResponse) {
                        return $responses[0];       // @codeCoverageIgnore
                    } else {
                        $png = Minecraft::generateAvatarFromSkin(Storage::disk('textures')->read($t->hash), $size);

                        ob_start();
                        imagepng($png);
                        imagedestroy($png);
                        $image = ob_get_contents();
                        ob_end_clean();

                        return Response::png($image);
                    }
                }
            } catch (Exception $e) {
                report($e);
            }
        }

        $png = imagecreatefromstring(base64_decode(static::getDefaultAvatar()));
        ob_start();
        imagepng($png);
        imagedestroy($png);
        $image = ob_get_contents();
        ob_end_clean();

        return Response::png($image);
    }

    public function avatarByTidWithSize($size, $tid)
    {
        return $this->avatarByTid($tid, $size);
    }

    public function avatar($base64_email, UserRepository $users, $size = 128)
    {
        $user = $users->get(base64_decode($base64_email), 'email');

        if ($user) {
            return $this->avatarByTid($user->getAvatarId());
        }

        $png = imagecreatefromstring(base64_decode(static::getDefaultAvatar()));
        ob_start();
        imagepng($png);
        imagedestroy($png);
        $image = ob_get_contents();
        ob_end_clean();

        return Response::png($image);
    }

    public function avatarWithSize($size, $base64_email, UserRepository $users)
    {
        return $this->avatar($base64_email, $users, $size);
    }

    public function preview($tid, $size = 250)
    {
        if ($t = Texture::find($tid)) {
            try {
                if (Storage::disk('textures')->has($t->hash)) {
                    $responses = event(new GetSkinPreview($t, $size));

                    if (isset($responses[0]) && $responses[0] instanceof \Symfony\Component\HttpFoundation\Response) {
                        return $responses[0];      // @codeCoverageIgnore
                    } else {
                        $binary = Storage::disk('textures')->read($t->hash);

                        if ($t->type == "cape") {
                            $png = Minecraft::generatePreviewFromCape($binary, $size*0.8, $size*1.125, $size);
                        } else {
                            $png = Minecraft::generatePreviewFromSkin($binary, $size, ($t->type == 'alex'), 'both', 4);
                        }

                        ob_start();
                        imagepng($png);
                        imagedestroy($png);
                        $image = ob_get_contents();
                        ob_end_clean();

                        return Response::png($image);
                    }
                }
            } catch (Exception $e) {
                report($e);
            }
        }

        // Show this if given texture is invalid.
        $png = imagecreatefromstring(base64_decode(static::getBrokenPreview()));
        ob_start();
        imagepng($png);
        imagedestroy($png);
        $image = ob_get_contents();
        ob_end_clean();

        return Response::png($image);
    }

    public function previewWithSize($size, $tid)
    {
        return $this->preview($tid, $size);
    }

    public function raw($tid) {
        if (!option('allow_downloading_texture')) {
            abort(404);
        }

        return ($t = Texture::find($tid))
            ? $this->texture($t->hash)
            : abort(404, trans('skinlib.non-existent'));
    }

    protected function getPlayerInstance($player_name)
    {
        $player = Player::where('player_name', $player_name)->first();

        if ($player->isBanned()) {
            abort(403, trans('general.player-banned'));
        }

        return $player;
    }

    public static function getDefaultAvatar()
    {
        return "iVBORw0KGgoAAAANSUhEUgAAAIAAAACACAIAAABMXPacAAACSElEQVR4nO3csUpbcQBGca/epBDTFio0KCLiC/QNRJBCod10LkUwfQq7uYhDKa5Ondri4NLRoX2GLl1EVGqhQcVEiYnSZ/jAy8lwfvN3r5LDf7lXU7yYfzqWGNzdR/vaxHil9x8Oh9E+VZZlpffPPh09OAPADAAzAMwAMAPADAAzAMwAMAPADAAzAMwAMAPADAAr0+fvqarfH0w1G9E+1eneVnp/TwDMADADwAwAMwDMADADwAwAMwDMADADwAwAMwDMADADwOI/ft9afxftG416+iNGyr/Lq2i/+flLtPcEwAwAMwDMADADwAwAMwDMADADwAwAMwDMADADwAwAMwCs+LrRji5o1B5F++tBv9L7n12cR/uFmVa0P/z9K9qnPAEwA8AMADMAzAAwA8AMADMAzAAwA8AMADMAzAAwA8AMACvT5++vNz5F+5XFrWi/9vIk2qfP93f2n0f7vR/b0f7bh/fR3hMAMwDMADADwAwAMwDMADADwAwAMwDMADADwAwAMwDMALBit/0quuBxay7ap+8bvh/8jPap1TfL0T79/4A/nU609wTADAAzAMwAMAPADAAzAMwAMAPADAAzAMwAMAPADAAzAKz4+HYpuqBeFNH+6Lgb7UfN9OxktO/2etHeEwAzAMwAMAPADAAzAMwAMAPADAAzAMwAMAPADAAzAMwAsPL0b/b9+71+9j5gZqoW7VOX3dto/+xJM9rf3Ayiffr7eAJgBoAZAGYAmAFgBoAZAGYAmAFgBoAZAGYAmAFgBoAZAPYfTCpLwD1OEBAAAAAASUVORK5CYII=";
    }

    public static function getBrokenPreview()
    {
        return "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";
    }

    /**
     * Default steve skin, base64 encoded.
     *
     * @see https://minecraft.gamepedia.com/File:Steve_skin.png
     * @return string
     */
    public static function getDefaultSteveSkin()
    {
        return 'iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAFDUlEQVR42u2a20sUURzH97G0LKMotPuWbVpslj1olJXdjCgyisowsSjzgrB0gSKyC5UF1ZNQWEEQSBQ9dHsIe+zJ/+nXfM/sb/rN4ZwZ96LOrnPgyxzP/M7Z+X7OZc96JpEISfWrFhK0YcU8knlozeJKunE4HahEqSc2nF6zSEkCgGCyb+82enyqybtCZQWAzdfVVFgBJJNJn1BWFgC49/VpwGVlD0CaxQiA5HSYEwBM5sMAdKTqygcAG9+8coHKY/XXAZhUNgDYuBSPjJL/GkzVVhAEU5tqK5XZ7cnFtHWtq/TahdSw2l0HUisr1UKIWJQBAMehDuqiDdzndsP2EZECAG1ZXaWMwOCODdXqysLf++uXUGv9MhUHIByDOijjdiSAoH3ErANQD73C7TXXuGOsFj1d4YH4OTJAEy8y9Hd0mCaeZ5z8dfp88zw1bVyiYhCLOg1ZeAqC0ybaDttHRGME1DhDeVWV26u17lRAPr2+mj7dvULfHw2q65fhQRrLXKDfIxkau3ZMCTGIRR3URR5toU38HbaPiMwUcKfBAkoun09PzrbQ2KWD1JJaqswjdeweoR93rirzyCMBCmIQizqoizZkm2H7iOgAcHrMHbbV9KijkUYv7qOn55sdc4fo250e+vUg4329/Xk6QB/6DtOws+dHDGJRB3XRBve+XARt+4hIrAF4UAzbnrY0ve07QW8uHfB+0LzqanMM7qVb+3f69LJrD90/1axiEIs6qIs21BTIToewfcSsA+Bfb2x67OoR1aPPzu2i60fSNHRwCw221Suz0O3jO+jh6V1KyCMGse9721XdN5ePutdsewxS30cwuMjtC860T5JUKpXyKbSByUn7psi5l+juDlZYGh9324GcPKbkycaN3jUSAGxb46IAYPNZzW0AzgiQ5tVnzLUpUDCAbakMQXXrOtX1UMtHn+Q9/X5L4wgl7t37r85OSrx+TYl379SCia9KXjxRpiTjIZTBFOvrV1f8ty2eY/T7XJ81FQAwmA8ASH1ob68r5PnBsxA88/xAMh6SpqW4HRnLBrkOA9Xv5wPAZjAUgOkB+SHxgBgR0qSMh0zmZRsmwDJm1gFg2PMDIC8/nAHIMls8x8GgzOsG5WiaqREgYzDvpTwjLDy8NM15LpexDEA3LepjU8Z64my+8PtDCmUyRr+fFwA2J0eAFYA0AxgSgMmYBMZTwFQnO9RNAEaHOj2DXF5UADmvAToA2ftyxZYA5BqgmZZApDkdAK4mAKo8GzPlr8G8AehzMAyA/i1girUA0HtYB2CaIkUBEHQ/cBHSvwF0AKZFS5M0ZwMQtEaEAmhtbSUoDADH9ff3++QZ4o0I957e+zYAMt6wHkhzpjkuAcgpwNcpA7AZDLsvpwiuOkBvxygA6Bsvb0HlaeKIF2EbADZpGiGzBsA0gnwQHGOhW2snRpbpPexbAB2Z1oicAMQpTnGKU5ziFKc4xSlOcYpTnOIUpzgVmgo+XC324WfJAdDO/+ceADkCpuMFiFKbApEHkOv7BfzfXt+5gpT8V7rpfYJcDz+jAsB233r6yyBsJ0mlBCDofuBJkel4vOwBFPv8fyYAFPJ+wbSf/88UANNRVy4Awo6+Ig2gkCmgA5DHWjoA+X7AlM//owLANkX0w0359od++pvX8fdMAcj3/QJ9iJsAFPQCxHSnQt8vMJ3v2wCYpkhkAOR7vG7q4aCXoMoSgG8hFAuc/grMdAD4B/kHl9da7Ne9AAAAAElFTkSuQmCC';
    }

    /**
     * Default alex skin, base64 encoded.
     *
     * @see https://minecraft.gamepedia.com/File:Alex_skin.png
     * @return string
     */
    public static function getDefaultAlexSkin() {
        return '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';
    }
}

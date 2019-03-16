<?php

namespace App\Http\Controllers;

use Event;
use Option;
use Storage;
use Response;
use Exception;
use Minecraft;
use App\Models\Player;
use App\Models\Texture;
use App\Events\GetSkinPreview;
use App\Events\GetAvatarPreview;
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
    public function json($player_name, $api = '')
    {
        $player = $this->getPlayerInstance($player_name);

        if ($api == 'csl') {
            $content = $player->getJsonProfile(Player::CSL_API);
        } elseif ($api == 'usm') {
            $content = $player->getJsonProfile(Player::USM_API);
        } else {
            $content = $player->getJsonProfile(Option::get('api_type'));
        }

        return Response::jsonProfile($content, 200, [
            'Last-Modified' => strtotime($player->last_modified),
        ]);
    }

    public function jsonWithApi($api, $player_name)
    {
        return $this->json($player_name, $api);
    }

    public function texture($hash, $headers = [], $message = '')
    {
        try {
            if (Storage::disk('textures')->has($hash)) {
                return Response::png(Storage::disk('textures')->get($hash), 200, array_merge([
                    'Last-Modified'  => Storage::disk('textures')->lastModified($hash),
                    'Accept-Ranges'  => 'bytes',
                    'Content-Length' => Storage::disk('textures')->size($hash),
                ], $headers));
            }
        } catch (Exception $e) {
            report($e);
        }

        return abort(404, $message);
    }

    public function textureWithApi($api, $hash)
    {
        return $this->texture($hash);
    }

    public function skin($player_name)
    {
        return $this->getBinaryTextureFromPlayer($player_name, 'skin');
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
     * @return Response
     */
    protected function getBinaryTextureFromPlayer($player_name, $type)
    {
        $player = $this->getPlayerInstance($player_name);

        if ($hash = $player->getTexture($type)) {
            return $this->texture($hash, [
                'Last-Modified'  => strtotime($player->last_modified),
            ], trans('general.texture-deleted'));
        } else {
            abort(404, trans('general.texture-not-uploaded', ['type' => $type]));
        }
    }

    // @codeCoverageIgnore

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

        return response()->file(storage_path('static_textures/avatar.png'));
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

        return response()->file(storage_path('static_textures/avatar.png'));
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

                        if ($t->type == 'cape') {
                            $png = Minecraft::generatePreviewFromCape($binary, $size * 0.8, $size * 1.125, $size);
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
        return response()->file(storage_path('static_textures/broken.png'));
    }

    public function previewWithSize($size, $tid)
    {
        return $this->preview($tid, $size);
    }

    public function raw($tid)
    {
        if (! option('allow_downloading_texture')) {
            abort(404);
        }

        return ($t = Texture::find($tid))
            ? $this->texture($t->hash)
            : abort(404, trans('skinlib.non-existent'));
    }

    public function avatarByPlayer($size, $name)
    {
        $player = Player::where('name', $name)->first();

        if (! $player) {
            return abort(404);
        }

        $hash = $player->getTexture('skin');
        if (Storage::disk('textures')->has($hash)) {
            $key = "avatar-{$hash}-{$size}";
            $png = Minecraft::generateAvatarFromSkin(
                Storage::disk('textures')->read($hash),
                $size
            );
            ob_start();
            imagepng($png);
            $image = ob_get_contents();
            ob_end_clean();
            imagedestroy($png);

            return Response::png($image);
        }

        return abort(404);
    }

    protected function getPlayerInstance($player_name)
    {
        $player = Player::where('name', $player_name)->first();

        if ($player->isBanned()) {
            abort(403, trans('general.player-banned'));
        }

        return $player;
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
}

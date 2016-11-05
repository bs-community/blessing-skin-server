<?php

namespace App\Services\Repositories;

use App\Models\User;
use App\Models\Player;
use Illuminate\Support\Arr;

class UserRepository extends Repository
{
    /**
     * Determine if a user exists in the repository.
     *
     * @param  string  $identification
     * @param  string  $type  Must be one of properties defined in User class
     * @return bool
     */
    public function has($identification, $type = 'uid')
    {
        if ($type == "uid") {
            return Arr::has($this->items, $identification);
        } else {
            Arr::where((array) $this->items, function($key, $value) use ($identification, $type) {
                return ($user->$type == $identification);
            });
        }
    }

    /**
     * Get a user from repository and cache it.
     *
     * @param  string  $identification
     * @param  string  $type
     * @return mixed
     */
    public function get($identification, $type = 'uid')
    {
        if (!$this->has($identification, $type)) {
            if ($type == "username") {
                $player = Player::where('player_name', $identification)->first();

                if ($player) {
                    $identification = $player->uid;
                    $type = "uid";
                } else {
                    return null;
                }
            }

            $user = User::where($type, $identification)->first();

            if ($user) {
                $this->set($user->uid, $user);
                return $user;
            }
        }

        return Arr::get($this->items, $identification, null);
    }
}

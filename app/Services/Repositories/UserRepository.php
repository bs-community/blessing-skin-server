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
            return (bool) Arr::where((array) $this->items, function($key, $value) use ($identification, $type) {
                if (property_exists($value, $type))
                    return false;

                return ($value->$type == $identification);
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

            return null;
        }

        $result = Arr::where((array) $this->items, function($key, $value) use ($identification, $type) {
            if (property_exists($value, $type))
                return false;

            return ($value->$type == $identification);
        });

        // return first element
        reset($result);
        return current($result);
    }

    public function getCurrentUser()
    {
        return $this->get(session('uid'));
    }
}

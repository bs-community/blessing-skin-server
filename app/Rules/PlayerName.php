<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class PlayerName implements Rule
{
    public function passes($attribute, $value)
    {
        $regexp = '/.*/';

        switch (option('player_name_rule')) {
            case 'official':
                // Mojang's official username rule
                $regexp = '/^[A-Za-z0-9_]+$/';
                break;

            case 'cjk':
                // CJK Unified Ideographs
                $regexp = '/^[A-Za-z0-9_§\x{4e00}-\x{9fff}]+$/u';
                break;

            case 'utf8':
                return mb_check_encoding($value, 'UTF-8');

            case 'custom':
                $regexp = option('custom_player_name_regexp') ?: $regexp;
                break;
        }

        return (bool) preg_match($regexp, $value);
    }

    public function message()
    {
        return trans('user.player.player-name-rule.'.option('player_name_rule'));
    }
}

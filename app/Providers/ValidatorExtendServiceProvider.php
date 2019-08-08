<?php

namespace App\Providers;

use Validator;
use Illuminate\Support\ServiceProvider;

class ValidatorExtendServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        /*
         * @param $a     attribute
         * @param $value value
         * @param $p     parameters
         * @param $v     validator
         */
        Validator::extend('no_special_chars', function ($a, $value, $p, $v) {
            return $value === e(addslashes(trim($value)));
        });

        Validator::extend('player_name', function ($a, $value, $p, $v) {
            $regexp = '/^(.*)$/';

            switch (option('player_name_rule')) {
                case 'official':
                    // Mojang's official username rule
                    $regexp = '/^([A-Za-z0-9_]+)$/';
                    break;

                case 'cjk':
                    // CJK Unified Ideographs
                    $regexp = '/^([A-Za-z0-9_\x{4e00}-\x{9fff}]+)$/u';
                    break;

                case 'custom':
                    $regexp = option('custom_player_name_regexp') ?: $regexp;
                    break;
            }

            return preg_match($regexp, $value);
        });
    }
}

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Validator;

class ValidatorExtendServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
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

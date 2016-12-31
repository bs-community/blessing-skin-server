<?php
/**
 * @Author: printempw
 * @Date:   2016-12-31 23:37:34
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-12-31 23:49:39
 */

if (!Illuminate\Support\Str::startsWith(option('update_source'), 'http')) {
    Option::set('update_source', config('options.update_source'));
}

foreach (config('options') as $key => $value) {
    if ($value === "true" || $value === "false") {
        $option = option($key);

        if ($option === "0" || $option === "1") {
            Option::set([$key => ($option === "0" ? "false" : "true")]);
        }
    }
}

Option::set('version', '3.2-pr8');

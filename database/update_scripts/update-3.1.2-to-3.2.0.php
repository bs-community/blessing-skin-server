<?php
/**
 * @Author: printempw
 * @Date:   2016-12-31 23:37:34
 * @Last Modified by:   printempw
 * @Last Modified time: 2017-01-02 16:22:32
 */
if (! Illuminate\Support\Str::startsWith(option('update_source'), 'http')) {
    Option::set('update_source', config('options.update_source'));
}

foreach (config('options') as $key => $value) {
    if ($value === 'true' || $value === 'false') {
        $option = option($key);

        if ($option === '0' || $option === '1') {
            Option::set([$key => ($option === '0' ? 'false' : 'true')]);
        }
    }
}

Option::set('version', '3.2.0');

return [
    'v3.2 新加入了插件系统，支持的插件请去程序发布帖查看',
];

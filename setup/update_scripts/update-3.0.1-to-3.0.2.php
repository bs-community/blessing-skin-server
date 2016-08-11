<?php
/**
 * @Author: printempw
 * @Date:   2016-08-11 13:08:13
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-08-11 13:25:23
 */

$options = [
    'avatar_query_string' => '0',
    'version'             => '',
    'check_update'        => '1',
    'update_source'       => 'github'
];

foreach ($options as $key => $value) {
    Option::add($key, $value);
}

Option::set('version', '3.0.2');

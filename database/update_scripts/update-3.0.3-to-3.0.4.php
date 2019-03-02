<?php
/**
 * @Author: printempw
 * @Date:   2016-08-27 18:21:04
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-08-27 18:21:15
 */
if (Option::get('update_source') == 'github') {
    Option::set('update_source', 'nyavm');
}

Option::set('version', '3.0.4');

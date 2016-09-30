<?php
/**
 * @Author: printempw
 * @Date:   2016-09-14 16:57:37
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-09-30 23:18:56
 */

function check_table_exists() {
    $tables = ['users', 'closets', 'players', 'textures', 'options'];

    foreach ($tables as $table_name) {
        // prefix will be added automatically
        if (!Database::hasTable($table_name)) {
            return false;
        }
    }

    return true;
}

function redirect_to($url, $msg = "") {
    // if ($msg !== "") {
    //     if (app()->bound('session')) {
    //         Session::put('msg', $msg);
    //         Session::save();
    //     } else {
            $_SESSION['msg'] = $msg;
    //     }
    // }

    if (!headers_sent()) {
        header('Location: '.$url);
    } else {
        echo "<meta http-equiv='Refresh' content='0; URL=$url'>";
    }
    exit;
}

/**
 * Check POST values in a simple way
 *
 * @param  array  $keys
 * @return void
 */
function check_post(Array $keys) {
    foreach ($keys as $key) {
        if (!isset($_POST[$key])) {
            return false;
        }
    }
    return true;
}

function check_password($password)
{
    if (strlen($password) > 16 || strlen($password) < 8) {
        return false;
    }
    return true;
}

function get_db_config()
{
    $config = require BASE_DIR.'/config/database.php';

    return $config['connections']['mysql'];
}

function check_db_config($config)
{
    @$conn = new mysqli($config['host'], $config['username'], $config['password'], $config['database'], $config['port']);

    if ($conn->connect_error) {
        throw new App\Exceptions\PrettyPageException("无法连接至 MySQL 服务器，请检查你的配置：".$conn->connect_error, $conn->connect_errno, true);
    }
}

function migrate($migration)
{
    if (strpos($migration, 'import') !== false) {
        $filename = BASE_DIR."/setup/migrations/".str_replace('-', '_', $migration).".php";
        if (file_exists($filename)) {
            return require $filename;
        }
    }
    throw new InvalidArgumentException('Non-existent migration');
}

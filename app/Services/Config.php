<?php

namespace App\Services;

use Illuminate\Database\Capsule\Manager as Capsule;
use App\Services\Schema;
use App\Exceptions\E;

class Config
{
    public static function getDbConfig()
    {
        return require BASE_DIR.'/config/database.php';
    }

    public static function getViewConfig()
    {
        return require BASE_DIR."/config/view.php";
    }

    /**
     * Check database config
     *
     * @param  array  $config
     * @return \MySQLi
     */
    public static function checkDbConfig(Array $config)
    {
        // use error control to hide shitty connect warnings
        @$conn = new \mysqli($config['host'], $config['username'], $config['password'], $config['database'], $config['port']);

        if ($conn->connect_error)
            throw new E("无法连接至 MySQL 服务器，请检查你的配置：".$conn->connect_error, $conn->connect_errno, true);

        $conn->query("SET names 'utf8'");
        return true;
    }

    public static function checkTableExist(Array $config)
    {
        $tables = ['users', 'closets', 'players', 'textures', 'options'];

        foreach ($tables as $table_name) {
            $table_name = $config['prefix'].$table_name;
            if (!Schema::hasTable($table_name)) {
                return false;
            }
        }
        return true;
    }

    public static function checkFolderExist()
    {
        if (!is_dir(BASE_DIR."/textures/"))
            throw new E("根目录下未发现 `textures` 文件夹，请先运行 <a href='./setup'>安装程序</a>，或者手动放置一个。", -1, true);

        $view_config = self::getViewConfig();

        if (!is_dir($view_config['cache_path']))
            mkdir($view_config['cache_path']);

        return true;
    }

    public static function checkDotEnvExist()
    {
        if (!file_exists(BASE_DIR."/.env"))
            exit('错误：.env 配置文件不存在');
        return true;
    }

}

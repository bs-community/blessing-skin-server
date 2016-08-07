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

    public static function checkPHPVersion()
    {
        if (strnatcasecmp(phpversion(), '5.5.9') < 0)
            throw new E('Blessing Skin Server v3 要求 PHP 版本不低于 5.5.9，当前版本为 '.phpversion(), -1, true);
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

    public static function checkTableExist()
    {
        $tables = ['users', 'closets', 'players', 'textures', 'options'];

        foreach ($tables as $table_name) {
            // prefix will be added automatically
            if (!Schema::hasTable($table_name)) {
                return false;
            }
        }

        return true;
    }

    public static function checkCache()
    {
        $view_config = self::getViewConfig();

        if (!is_dir($view_config['cache_path'])) {
            if (!mkdir($view_config['cache_path']))
                throw new E('缓存文件夹创建失败，请确认目录权限是否正确', -1);
        }

        return true;
    }

    public static function checkDotEnvExist()
    {
        if (!file_exists(BASE_DIR."/.env"))
            exit('错误：.env 配置文件不存在');
        return true;
    }

}

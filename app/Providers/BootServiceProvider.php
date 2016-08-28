<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Exceptions\E;

class BootServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \View::addExtension('tpl', 'blade');
        $this->checkDbConfig();
        $this->checkInstallation();
    }

    protected function checkDbConfig()
    {
        // use error control to hide shitty connect warnings
        @$conn = new \mysqli(
            config('database.connections.mysql.host'),
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password'),
            config('database.connections.mysql.database'),
            config('database.connections.mysql.port')
        );

        if ($conn->connect_error)
            throw new E("无法连接至 MySQL 服务器，请检查你的配置：".$conn->connect_error, $conn->connect_errno, true);

        return true;
    }

    protected function checkInstallation()
    {
        if (!$this->checkTableExist()) {
            \Http::redirect(url('/setup/index.php'));
        }

        if (!is_dir(BASE_DIR.'/textures/')) {
            throw new E("检测到 `textures` 文件夹已被删除，请重新运行 <a href='".url('setup')."'>安装程序</a>，或者手动放置一个。", -1, true);
        }

        if (config('app.version') != \Option::get('version', '')) {
            \Http::redirect(url('/setup/update.php'));
        }

        return true;
    }

    public static function checkTableExist()
    {
        $tables = ['users', 'closets', 'players', 'textures', 'options'];

        foreach ($tables as $table_name) {
            // prefix will be added automatically
            if (!\Schema::hasTable($table_name)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

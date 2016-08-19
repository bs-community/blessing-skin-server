<?php

namespace App\Services;

class Migration
{
    /**
     * Create tables, prefix will be added automatically
     *
     * @return void
     */
    public static function creatTables()
    {
        require BASE_DIR."/setup/tables.php";
    }

    public static function __callStatic($method, $args)
    {
        if (strpos($method, 'import') !== false) {
            $filename = BASE_DIR."/setup/migrations/".snake_case($method).".php";
            if (Storage::exist($filename)) {
                return require $filename;
            }
        }
        throw new \InvalidArgumentException('Non-existent migration');
    }

}

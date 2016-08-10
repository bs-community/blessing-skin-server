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

    public static function importV2Textures()
    {
        return require BASE_DIR."/setup/migrations/import_v2_textures.php";
    }

}

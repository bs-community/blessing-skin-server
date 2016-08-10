<?php

namespace App\Services;

class Application
{
    /**
     * Start Application
     *
     * @return void
     */
    public function run()
    {
        Boot::start();
    }

    /**
     * Get current app version
     *
     * @return string
     */
    public static function getVersion()
    {
        $config = require BASE_DIR."/config/app.php";
        return $config['version'];
    }

}

<?php
/**
 * Migrations Bootstrap of Blessing Skin Server
 */

// Define Base Directory
define('BASE_DIR', dirname(dirname(__FILE__)));

// Register Composer Auto Loader
require BASE_DIR.'/vendor/autoload.php';

// Boot Services
Blessing\Foundation\Boot::loadServices();
Config::checkPHPVersion();
Boot::loadDotEnv(BASE_DIR);
Boot::registerErrorHandler(new \Whoops\Handler\PrettyPageHandler);
Boot::startSession();

$db_config = Config::getDbConfig();

// Boot Eloquent to make Schema available
if (Config::checkDbConfig($db_config)) {
    Boot::bootEloquent($db_config);
}

// If no update is available
if (App::getVersion() == Option::get('version', '')) {
    View::show('setup.locked');
    exit;
}

$step = isset($_GET['step']) ? $_GET['step'] : '1';

switch ($step) {
    case '1':
        View::show('setup.updates.welcome');
        break;

    case '2':
        $resource = opendir(BASE_DIR."/setup/update_scripts/");
        $update_script_exist = false;
        while($filename = @readdir($resource)) {
            if ($filename != "." && $filename != "..") {
                preg_match('/update-(.*)-to-(.*).php/', $filename, $matches);

                if (isset($matches[2])) {
                    $update_script_exist = ($matches[2] == App::getVersion());
                } else {
                    continue;
                }

                include BASE_DIR."/setup/update_scripts/$filename";
            }
        }
        closedir($resource);

        if (!$update_script_exist) {
            // if update script is not given
            Option::set('version', App::getVersion());
        }

        View::show('setup.updates.success');

        break;

    default:
        throw new App\Exceptions\E('非法参数', 1, true);
        break;
}


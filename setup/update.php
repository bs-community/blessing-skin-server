<?php
/**
 * Migrations Bootstrap of Blessing Skin Server
 */

// Define Base Directory
define('BASE_DIR', dirname(dirname(__FILE__)));

// Register Composer Auto Loader
require BASE_DIR.'/vendor/autoload.php';

// Initialize Application
$app = new Blessing\Foundation\Application();
$app->boot();
Boot::registerErrorHandler(new \Whoops\Handler\PrettyPageHandler);

$db_config = Config::getDbConfig();

// If no update is available
if (App::version() == Option::get('version', '')) {
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
                    $update_script_exist = ($matches[2] == App::version());
                } else {
                    continue;
                }

                include BASE_DIR."/setup/update_scripts/$filename";
            }
        }
        closedir($resource);

        if (!$update_script_exist) {
            // if update script is not given
            Option::set('version', App::version());
        }

        View::show('setup.updates.success');

        break;

    default:
        throw new App\Exceptions\E('非法参数', 1, true);
        break;
}


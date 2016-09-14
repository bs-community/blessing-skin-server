<?php
/**
 * Update Bootstrap of Blessing Skin Server
 */

require __DIR__."/includes/bootstrap.php";

// If no update is available
if (config('app.version') == Option::get('version', '')) {
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
                    $update_script_exist = ($matches[2] == config('app.version'));
                } else {
                    continue;
                }

                include BASE_DIR."/setup/update_scripts/$filename";
            }
        }
        closedir($resource);

        foreach (config('options') as $key => $value) {
            if (!Option::has($key))
                Option::set($key, $value);
        }

        if (!$update_script_exist) {
            // if update script is not given
            Option::set('version', config('app.version'));
        }

        View::show('setup.updates.success');

        break;

    default:
        throw new App\Exceptions\PrettyPageException('非法参数', 1, true);
        break;
}


<?php
/**
 * Installation of Blessing Skin Server
 */

require __DIR__."/includes/bootstrap.php";

// If already installed
if (check_table_exists()) {
    View::show('setup.locked');
    exit;
}

$step = isset($_GET['step']) ? $_GET['step'] : 1;

/*
 * Stepped installation
 */
switch ($step) {
    case 1:
        $server = $db_config['username']."@".$db_config['host'];
        echo View::make('setup.steps.1')->with('server', $server);
        break;

    case 2:
        echo View::make('setup.steps.2');
        break;

    case 3:
        // check post
        if (check_post(['email', 'password', 'confirm-pwd'], true))
        {
            if ($_POST['password'] != $_POST['confirm-pwd'])
                redirect_to('index.php?step=2', '确认密码不一致');

            $email    = $_POST['email'];
            $password = $_POST['password'];
            $sitename = isset($_POST['sitename']) ? $_POST['sitename'] : "Blessing Skin Server";

            if (validate($email, 'email')) {
                if (!check_password($password)) {
                    redirect_to('index.php?step=2', '无效的密码。密码长度应该大于 8 并小于 16。');

                } else if (e($password) != $password) {
                    redirect_to('index.php?step=2', '无效的密码。密码中包含了奇怪的字符。');
                }
            } else {
                redirect_to('index.php?step=2', '邮箱格式不正确。');
            }
        }
        else {
            redirect_to('index.php?step=2', '表单信息不完整。');
        }

        // create tables
        require BASE_DIR."/setup/includes/tables.php";

        // import options
        $options = require BASE_DIR."/config/options.php";
        $options['site_name']    = $_POST['sitename'];
        $options['site_url']     = url('/');
        $options['version']      = config('app.version');
        $options['announcement'] = str_replace('{version}', $options['version'], $options['announcement']);

        foreach ($options as $key => $value) {
            Option::set($key, $value);
        }

        // register super admin
        $user = App\Models\User::register($_POST['email'], $_POST['password'], function($user) {
            $user->ip           = get_real_ip();
            $user->score        = option('user_initial_score');
            $user->register_at  = Utils::getTimeFormatted();
            $user->last_sign_at = Utils::getTimeFormatted(time() - 86400);
            $user->permission   = App\Models\User::SUPER_ADMIN;
        });

        if (!is_dir(BASE_DIR.'/storage/textures/')) {
            if (!mkdir(BASE_DIR.'/storage/textures/'))
                throw new App\Exceptions\PrettyPageException('/storage/textures 文件夹创建失败，请检查目录权限是否正确，或者手动放置一个。', -1);
        }

        echo View::make('setup.steps.3')->with('email', $_POST['email'])->with('password', $_POST['password']);

        break;

    default:
        throw new App\Exceptions\PrettyPageException('非法参数', 1, true);
        break;
}

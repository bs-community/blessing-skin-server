<?php
/**
 * Installation of Blessing Skin Server
 */

// Define Base Directory
define('BASE_DIR', dirname(dirname(__FILE__)));

// Register Composer Auto Loader
require BASE_DIR.'/vendor/autoload.php';

// Load Aliases
App\Services\Boot::loadServices();

// Check Runtime Environment
Boot::checkRuntimeEnv();

// Load dotenv Configuration
Boot::loadDotEnv(BASE_DIR);

// Register Error Handler
Boot::registerErrorHandler();

$db_config = Config::getDbConfig();

// Boot Eloquent to make Schema available
if (Config::checkDbConfig($db_config)) {
    Boot::bootEloquent($db_config);
}

Boot::startSession();

// If already installed
if (Config::checkTableExist($db_config)) {
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
        if (isset($_POST['email']) && isset($_POST['password']) && isset($_POST['confirm-pwd']))
        {
            if ($_POST['password'] != $_POST['confirm-pwd'])
                Http::redirect('index.php?step=2', '确认密码不一致');

            $email    = $_POST['email'];
            $password = $_POST['password'];
            $sitename = isset($_POST['sitename']) ? $_POST['sitename'] : "Blessing Skin Server";

            if (Validate::checkValidEmail($email)) {
                if (strlen($password) > 16 || strlen($password) < 8) {
                    Http::redirect('index.php?step=2', '无效的密码。密码长度应该大于 8 并小于 16。');

                } else if (Utils::convertString($password) != $password) {
                    Http::redirect('index.php?step=2', '无效的密码。密码中包含了奇怪的字符。');
                }
            } else {
                Http::redirect('index.php?step=2', '邮箱格式不正确。');
            }
        } else {
            Http::redirect('index.php?step=2', '表单信息不完整。');
        }

        Migration::creatTables($db_config['prefix']);

        $options = [
            'site_url'                 => Http::getBaseUrl(),
            'site_name'                => $_POST['sitename'],
            'site_description'         => '开源的 PHP Minecraft 皮肤站',
            'user_can_register'        => '1',
            'regs_per_ip'              => '3',
            'api_type'                 => '0',
            'announcement'             => '欢迎使用 Blessing Skin Server 3.0！',
            'color_scheme'             => 'skin-blue',
            'home_pic_url'             => './assets/images/bg.jpg',
            'current_version'          => '3.0-beta',
            'custom_css'               => '',
            'custom_js'                => '',
            'update_url'               => 'https://work.prinzeugen.net/update.json',
            'allow_chinese_playername' => '1',
            'show_footer_copyright'    => '1',
            'comment_script'           => '',
            'user_initial_score'       => '1000',
            'sign_gap_time'            => '24'
        ];

        foreach ($options as $key => $value) {
            Option::add($key, $value);
        }

        $user = new App\Models\User($_POST['email']);
        $user->register($_POST['password'], Http::getRealIP());

        if (!is_dir(BASE_DIR.'/textures/')) {
            if (!mkdir(BASE_DIR.'/textures/'))
                throw new E('textures 文件夹创建失败，请确认目录权限是否正确，或者手动放置一个。', -1);
        }

        echo View::make('setup.steps.3')->with('email', $_POST['email'])->with('password', $_POST['password']);

        break;
}

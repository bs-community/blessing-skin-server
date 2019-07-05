<?php

return [
    '请您手动打开终端或 PowerShell 执行以下命令以完成升级：',
    'Please open terminal or PowerShell to complete upgrade:',
    '<code>php artisan migrate --force</code>',
    '',
    '然后修改 .env 文件，将 <code>QUEUE_DRIVER</code> 的值改为 <code>database</code>。（使用 Redis 的用户请修改为 <code>redis</code>）',
    'Then, please edit your ".env" file. Change the value of <code>QUEUE_DRIVER</code> from <code>sync</code> to <code>database</code>. Redis users please change it to <code>redis</code>.',
];

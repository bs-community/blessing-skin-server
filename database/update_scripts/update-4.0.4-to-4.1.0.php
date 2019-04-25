<?php

use Artisan;

Artisan::call('jwt:secret', ['--no-interaction' => true]);
Artisan::call('migrate', ['--force' => true]);
Artisan::call('passport:keys', ['--no-interaction' => true]);

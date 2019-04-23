<?php

use Artisan;

Artisan::call('jwt:secret', ['--no-interaction' => true]);

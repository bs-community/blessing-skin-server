<?php

Artisan::call('migrate', ['--force' => true]);

if (option('home_pic_url') === './app/bg.jpg') {
    option(['home_pic_url' => './app/bg.png']);
}

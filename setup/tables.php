<?php
/**
 * @Author: printempw
 * @Date:   2016-08-06 19:39:12
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-08-06 19:39:25
 */

Schema::create('users', function($table) {
    $table->increments('uid');
    $table->string('email', 100);
    $table->string('nickname', 50);
    $table->integer('score');
    $table->integer('avatar')->default('0');
    $table->string('password', 255);
    $table->string('ip', 32);
    $table->integer('permission')->default('0');
    $table->dateTime('last_sign_at');
    $table->dateTime('register_at');
});

Schema::create('closets', function($table) {
    $table->increments('uid');
    $table->longText('textures')->default('');
});

Schema::create('players', function($table) {
    $table->increments('pid');
    $table->integer('uid');
    $table->string('player_name', 50);
    $table->string('preference', 10);
    $table->integer('tid_steve')->default('0');
    $table->integer('tid_alex')->default('0');
    $table->integer('tid_cape')->default('0');
    $table->dateTime('last_modified');
});

Schema::create('textures', function($table) {
    $table->increments('tid');
    $table->string('name', 50);
    $table->string('type', 10);
    $table->integer('likes');
    $table->string('hash', 64);
    $table->integer('size');
    $table->integer('uploader');
    $table->integer('public');
    $table->dateTime('upload_at');
});

Schema::create('options', function($table) {
    $table->increments('id');
    $table->string('option_name', 50);
    $table->longText('option_value');
});

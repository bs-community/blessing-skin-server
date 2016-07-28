<?php

namespace App\Services;

class Migration
{
    public static function creatTables($prefix = "")
    {
        Schema::create($prefix.'users', function($table) {
            $table->increments('uid');
            $table->string('email', 100);
            $table->string('nickname', 50);
            $table->integer('score');
            $table->integer('avatar');
            $table->string('password', 255);
            $table->string('ip', 32);
            $table->integer('permission')->default('0');
            $table->dateTime('last_sign_at');
            $table->dateTime('register_at');
        });

        Schema::create($prefix.'closets', function($table) {
            $table->increments('uid');
            $table->longText('textures');
        });

        Schema::create($prefix.'players', function($table) {
            $table->increments('pid');
            $table->integer('uid');
            $table->string('player_name', 50);
            $table->string('preference', 10);
            $table->integer('tid_steve');
            $table->integer('tid_alex');
            $table->integer('tid_cape');
            $table->dateTime('last_modified');
        });

        Schema::create($prefix.'textures', function($table) {
            $table->increments('tid');
            $table->string('name', 50);
            $table->string('type', 10);
            $table->integer('likes');
            $table->string('hash', 64);
            $table->integer('size');
            $table->integer('uploader');
            $table->integer('public');
            $table->dateTime('last_modified');
        });

        Schema::create($prefix.'options', function($table) {
            $table->increments('id');
            $table->string('option_name', 50);
            $table->longText('option_value');
        });

    }
}

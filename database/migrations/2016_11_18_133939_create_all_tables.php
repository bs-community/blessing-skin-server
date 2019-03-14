<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAllTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('uid');
            $table->string('email', 100);
            $table->string('nickname', 50)->default('');
            $table->integer('score');
            $table->integer('avatar')->default('0');
            $table->string('password', 255);
            $table->string('ip', 32);
            $table->integer('permission')->default('0');
            $table->dateTime('last_sign_at');
            $table->dateTime('register_at');
        });

        Schema::create('players', function (Blueprint $table) {
            $table->increments('pid');
            $table->integer('uid');
            $table->string('player_name', 50);
            $table->integer('tid_cape')->default('0');
            $table->dateTime('last_modified');
        });

        Schema::create('textures', function (Blueprint $table) {
            $table->increments('tid');
            $table->string('name', 50);
            $table->string('type', 10);
            $table->string('hash', 64);
            $table->integer('size');
            $table->integer('uploader');
            $table->tinyInteger('public');
            $table->dateTime('upload_at');
        });

        Schema::create('options', function (Blueprint $table) {
            $table->increments('id');
            $table->string('option_name', 50);
            $table->longText('option_value');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
        Schema::drop('players');
        Schema::drop('textures');
        Schema::drop('options');
    }
}

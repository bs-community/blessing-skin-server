<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLanguageLinesTable extends Migration
{
    public function up()
    {
        Schema::create('language_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->string('group');
            $table->index('group');
            $table->string('key');
            $table->text('text');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('language_lines');
    }
}

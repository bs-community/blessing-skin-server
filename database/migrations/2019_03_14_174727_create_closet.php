<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCloset extends Migration
{
    public function up()
    {
        Schema::create('user_closet', function (Blueprint $table) {
            $table->integer('user_uid');
            $table->integer('texture_tid');
            $table->text('item_name')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_closet');
    }
}

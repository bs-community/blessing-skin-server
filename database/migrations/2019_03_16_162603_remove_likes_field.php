<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveLikesField extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('textures', 'likes')) {
            Schema::table('textures', function (Blueprint $table) {
                $table->dropColumn('likes');
            });
        }
    }

    public function down()
    {
    }
}

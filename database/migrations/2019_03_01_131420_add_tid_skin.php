<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTidSkin extends Migration
{
    public function up()
    {
        Schema::table('players', function (Blueprint $table) {
            $table->integer('tid_skin')->default(-1);

            if (Schema::hasColumn('players', 'preference')) {
                $table->string('preference', 10)->nullable()->change();
            }
        });
    }

    public function down()
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn('tid_skin');

            if (Schema::hasColumn('players', 'preference')) {
                $table->string('preference', 10)->nullable(false)->change();
            }
        });
    }
}

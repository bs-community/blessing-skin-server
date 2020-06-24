<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenamePlayersTableColumns extends Migration
{
    public function up()
    {
        Schema::table('players', function (Blueprint $table) {
            $table->renameColumn('player_name', 'name');
        });
    }

    public function down()
    {
        Schema::table('players', function (Blueprint $table) {
            $table->renameColumn('name', 'player_name');
        });
    }
}

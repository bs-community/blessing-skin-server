<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRememberTokenToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->rememberToken();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (config('database.default') == 'sqlite') {
                // Dropping columns from a SQLite database requires `doctrine/dbal` dependency.
                // However, we won't install it because it's too hard to specify the version of
                // all the new dependencies exactly to make them support PHP ^5.5.9. Damn it.
                return;
            }

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('remember_token');
            });
        });
    }
}

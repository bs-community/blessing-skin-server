<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVerificationToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('verified')->default(false);
            $table->string('verification_token')->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (config('database.default') == 'sqlite') {
            // Dropping columns from a SQLite database requires `doctrine/dbal` dependency.
            // However, we won't install it because it's too hard to specify the version of
            // all the new dependencies exactly to make them support PHP ^5.5.9. Damn it.
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('verified');
            $table->dropColumn('verification_token');
        });
    }
}

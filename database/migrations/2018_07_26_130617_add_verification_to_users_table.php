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
        // Separate these commands apart since
        // dropping multiple columns within a single migration
        // while using a SQLite database is not supported.
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('verified');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('verification_token');
        });
    }
}

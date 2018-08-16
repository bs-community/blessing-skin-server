<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ImportOptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // import options
        $options = config('options');

        $options['version'] = config('app.version');

        $options['announcement'] = str_replace(
            '{version}',
            $options['version'],
            $options['announcement']
        );

        $options['copyright_text'] = str_replace(
            '{year}',
            Carbon\Carbon::now()->year,
            $options['copyright_text']
        );

        foreach ($options as $key => $value) {
            Option::set($key, $value);
        }

        Option::save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('options')->delete();
    }
}

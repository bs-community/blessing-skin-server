<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('reports')) {
            Schema::create('reports', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('tid');
                $table->integer('uploader');
                $table->integer('reporter');
                $table->longText('reason');
                $table->integer('status');
                $table->dateTime('report_at');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('reports');
    }
}

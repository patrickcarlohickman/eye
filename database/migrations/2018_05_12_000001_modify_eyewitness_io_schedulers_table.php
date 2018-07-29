<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyEyewitnessIoSchedulersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('eyewitness.eyewitness_database_connection'))->table('eyewitness_io_schedulers', function (Blueprint $table) {
            $table->timestamp('last_run')->nullable()->default(null)->index();
            $table->integer('alert_last_run_greater_than')->default(0)->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('eyewitness.eyewitness_database_connection'))->table('eyewitness_io_schedulers', function (Blueprint $table) {
            $table->dropColumn('last_run');
        });
        Schema::connection(config('eyewitness.eyewitness_database_connection'))->table('eyewitness_io_schedulers', function (Blueprint $table) {
            $table->dropColumn('alert_last_run_greater_than');
        });
    }
}

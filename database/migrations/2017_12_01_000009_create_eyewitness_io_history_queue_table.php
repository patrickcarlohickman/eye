<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEyewitnessIoHistoryQueueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('eyewitness.eyewitness_database_connection'))->create('eyewitness_io_history_queue', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('queue_id')->unsigned()->index();
            $table->integer('pending_count')->unsigned()->default(0);
            $table->integer('failed_count')->unsigned()->default(0);
            $table->integer('exception_count')->unsigned()->default(0);
            $table->decimal('sonar_time', 10,2)->unsigned()->default(0);
            $table->integer('sonar_count')->unsigned()->default(0);
            $table->decimal('process_time', 10,2)->unsigned()->default(0);
            $table->integer('process_count')->unsigned()->default(0);
            $table->integer('idle_time')->unsigned()->default(0);
            $table->integer('sonar_deployed')->nullable()->default(null);
            $table->integer('hour');
            $table->date('date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('eyewitness.eyewitness_database_connection'))->dropIfExists('eyewitness_io_history_queue');
    }
}

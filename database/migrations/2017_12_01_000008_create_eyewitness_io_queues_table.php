<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEyewitnessIoQueuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('eyewitness.eyewitness_database_connection'))->create('eyewitness_io_queues', function (Blueprint $table) {
            $table->increments('id');
            $table->string('connection', 191);
            $table->string('tube', 191);
            $table->string('driver', 191);
            $table->decimal('current_wait_time', 10, 2)->default(0);
            $table->boolean('healthy')->nullable()->default(null)->index();
            $table->boolean('alert_on_failed_job')->default(true);
            $table->integer('alert_heartbeat_greater_than')->default(120);
            $table->integer('alert_pending_jobs_greater_than')->default(0);
            $table->integer('alert_failed_jobs_greater_than')->default(0);
            $table->integer('alert_wait_time_greater_than')->default(0);
            $table->timestamp('last_heartbeat')->nullable()->default(null)->index();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('eyewitness.eyewitness_database_connection'))->dropIfExists('eyewitness_io_queues');
    }
}

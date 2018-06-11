<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEyewitnessIoHistorySchedulerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('eyewitness.eyewitness_database_connection'))->create('eyewitness_io_history_scheduler', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('scheduler_id')->unsigned()->index();
            $table->decimal('time_to_run', 10, 4)->nullable()->default(null);
            $table->integer('exitcode')->index()->nullable()->default(null);
            $table->text('output')->nullable()->default(null);
            $table->boolean('overdue')->default(false);
            $table->timestamp('expected_completion')->index()->nullable()->default(null);
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('eyewitness.eyewitness_database_connection'))->dropIfExists('eyewitness_io_history_scheduler');
    }
}

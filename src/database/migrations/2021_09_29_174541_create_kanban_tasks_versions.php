<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKanbanTasksVersions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kanban_tasks_versions', function (Blueprint $table) {
            $table->id();
            $table->integer('index')->nullable();
            $table->string('name');
            $table->dateTime('deadline')->nullable();
            $table->unsignedBigInteger('shared_task_data_id')->nullable();
            $table->unsignedBigInteger('reporter_id')->nullable();
            $table->unsignedBigInteger('column_id')->nullable();
            $table->unsignedBigInteger('row_id')->nullable();
            $table->unsignedBigInteger('board_id')->nullable();
            $table->unsignedBigInteger('badge_id')->nullable();
            $table->unsignedBigInteger('task_id')->nullable();
            $table->unsignedBigInteger('log_id')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kanban_tasks_versions');
    }
}

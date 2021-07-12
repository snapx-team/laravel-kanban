<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKanbanTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kanban_tasks', function (Blueprint $table) {
            $table->id();
            $table->integer('index')->nullable();
            $table->string('name');
            $table->text('description');
            $table->dateTime('deadline')->nullable();
            $table->unsignedBigInteger('reporter_id')->nullable();
            $table->unsignedBigInteger('column_id')->nullable();
            $table->unsignedBigInteger('row_id')->nullable();
            $table->unsignedBigInteger('board_id')->nullable();
            $table->unsignedBigInteger('badge_id')->nullable();
            $table->unsignedBigInteger('erp_employee_id')->nullable();
            $table->unsignedBigInteger('erp_job_site_id')->nullable();
            $table->string('group');
            $table->string('status')->default('active');
            $table->timestamps();
            $table->foreign('column_id')->references('id')->on('kanban_columns')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kanban_tasks');
    }
}

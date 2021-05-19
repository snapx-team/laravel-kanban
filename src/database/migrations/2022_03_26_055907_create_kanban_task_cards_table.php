<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKanbanTaskCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kanban_task_cards', function (Blueprint $table) {
            $table->id();
            $table->integer('index');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('column_id');
            $table->unsignedBigInteger('member_id');
            $table->timestamps();
            $table->foreign('employee_id')->references('id')->on('kanban_employees')->onDelete('cascade');
            $table->foreign('column_id')->references('id')->on('kanban_columns')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('kanban_members')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kanban_task_cards');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKanbanGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kanban_groups', function (Blueprint $table) {
            $table->id();
            $table->integer('group')->unique();
            $table->unsignedBigInteger('task_id');

            $table->timestamps();
            $table->foreign('task_id')->references('id')->on('kanban_tasks')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kanban_groups');
    }
}

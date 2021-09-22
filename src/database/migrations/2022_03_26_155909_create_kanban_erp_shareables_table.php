<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKanbanErpShareablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kanban_erp_shareables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shared_task_data_id');
            $table->unsignedBigInteger('shareable_id');
            $table->string('shareable_type');
            $table->timestamps();
            $table->foreign('shared_task_data_id')->references('id')->on('kanban_shared_task_data')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kanban_erp_shareables');
    }
}

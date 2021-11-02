<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKanbanEmployeeBoardNotificationSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kanban_employee_board_notification_setting', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('board_id');
            $table->unsignedBigInteger('employee_id');
            $table->text('ignore_types')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('board_id')->references('id')->on('kanban_boards')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('kanban_employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kanban_employee_board_notification_setting');
    }
}

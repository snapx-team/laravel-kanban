<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateColumnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kanban_columns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('row_id');
            $table->string('name');
            $table->string('shift_start')->index();
            $table->string('shift_end')->index();
            $table->timestamps();
            $table->foreign('row_id')->references('id')->on('kanban_rows')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kanban_columns');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModProjecttasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mod_projecttasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('projectid')->length(10)->unsigned();
            $table->text('task');
            $table->text('notes');
            $table->integer('adminid')->unsigned();
            $table->datetime('created');
            $table->date('duedate');
            $table->integer('completed')->length(1)->unsigned();
            $table->integer('billed')->length(1)->unsigned();
            $table->integer('order')->length(3)->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mod_projecttasks');
    }
}

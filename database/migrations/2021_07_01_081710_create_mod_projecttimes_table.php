<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModProjecttimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mod_projecttimes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('projectid')->length(10)->unsigned();
            $table->integer('taskid')->length(10)->unsigned();
            $table->string('adminid');
            $table->string('start');
            $table->string('end');
            $table->integer('donotbill')->length(1);
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mod_projecttimes');
    }
}

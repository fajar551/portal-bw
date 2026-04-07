<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModProjectlogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mod_projectlog', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('projectid')->unsigned();
            $table->datetime('date');
            $table->string('msg');
            $table->integer('adminid')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mod_projectlog');
    }
}

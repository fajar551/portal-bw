<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModProjectmessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mod_projectmessages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('projectid')->unsigned();
            $table->datetime('date');
            $table->text('message');
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
        Schema::dropIfExists('mod_projectmessages');
    }
}

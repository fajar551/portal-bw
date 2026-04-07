<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModProjectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mod_project', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('userid')->unsigned(); ;
            $table->text('title');
            $table->text('ticketids');
            $table->text('invoiceids');
            $table->text('notes');
            $table->integer('adminid')->unsigned(); ;
            $table->string('status');
            $table->date('created');
            $table->date('duedate');
            $table->integer('completed')->length(1)->unsigned();
            $table->datetime('lastmodified');
            $table->text('watchers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mod_project');
    }
}

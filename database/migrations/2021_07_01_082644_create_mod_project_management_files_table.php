<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModProjectManagementFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mod_project_management_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('project_id')->length(10)->unsigned();
            $table->integer('message_id')->length(10)->unsigned();
            $table->string('filename',256)->default('');
            $table->integer('admin_id')->length(10)->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mod_project_management_files');
    }
}

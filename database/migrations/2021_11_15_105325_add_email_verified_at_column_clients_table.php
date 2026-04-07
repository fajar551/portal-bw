<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmailVerifiedAtColumnClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tblclients')) {
            Schema::table('tblclients', function (Blueprint $table) {
                //
                $table->timestamp('email_verified_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('tblclients')) {
            Schema::table('tblclients', function (Blueprint $table) {
                //
                $table->dropColumn('email_verified_at');
            });
        }
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('empresa_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
//            $table->foreign('grupo_cloud_id')->references('id')->on('grupo_clouds')->onUpdate('RESTRICT')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_empresa_id_foreign');
//            $table->dropForeign('users_grupo_cloud_id_foreign');
        });
    }
}

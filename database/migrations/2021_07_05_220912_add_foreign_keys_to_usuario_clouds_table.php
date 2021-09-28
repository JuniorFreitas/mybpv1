<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToUsuarioCloudsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('usuario_clouds', function (Blueprint $table) {
            $table->foreign('grupo_cloud_id')->references('id')->on('grupo_clouds')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('usuario_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('usuario_clouds', function (Blueprint $table) {
            $table->dropForeign('usuario_clouds_grupo_cloud_id_foreign');
            $table->dropForeign('usuario_clouds_usuario_id_foreign');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToGrupoHabilidadeCloudTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('grupo_habilidade_cloud', function (Blueprint $table) {
            $table->foreign('grupo_cloud_id')->references('id')->on('grupo_clouds')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('habilidade_cloud_id')->references('id')->on('habilidade_clouds')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('grupo_habilidade_cloud', function (Blueprint $table) {
            $table->dropForeign('grupo_habilidade_cloud_grupo_cloud_id_foreign');
            $table->dropForeign('grupo_habilidade_cloud_habilidade_cloud_id_foreign');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGrupoHabilidadeCloudTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grupo_habilidade_cloud', function (Blueprint $table) {
            $table->unsignedBigInteger('grupo_cloud_id')->index('grupo_habilidade_cloud_grupo_cloud_id_foreign');
            $table->unsignedBigInteger('habilidade_cloud_id')->index('grupo_habilidade_cloud_habilidade_cloud_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('grupo_habilidade_cloud');
    }
}

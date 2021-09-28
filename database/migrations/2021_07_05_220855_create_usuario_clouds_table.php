<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsuarioCloudsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usuario_clouds', function (Blueprint $table) {
            $table->unsignedBigInteger('usuario_id')->index('usuario_clouds_usuario_id_foreign');
            $table->unsignedBigInteger('grupo_cloud_id')->index('usuario_clouds_grupo_cloud_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usuario_clouds');
    }
}

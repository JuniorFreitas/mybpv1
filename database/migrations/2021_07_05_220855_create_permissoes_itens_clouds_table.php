<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissoesItensCloudsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permissoes_itens_clouds', function (Blueprint $table) {
            $table->unsignedBigInteger('item_id')->index('permissoes_itens_clouds_item_id_foreign');
            $table->unsignedBigInteger('grupo_cloud_id')->index('permissoes_itens_clouds_grupo_cloud_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permissoes_itens_clouds');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToPermissoesItensCloudsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('permissoes_itens_clouds', function (Blueprint $table) {
            $table->foreign('grupo_cloud_id')->references('id')->on('grupo_clouds')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('item_id')->references('id')->on('itens_cloud')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('permissoes_itens_clouds', function (Blueprint $table) {
            $table->dropForeign('permissoes_itens_clouds_grupo_cloud_id_foreign');
            $table->dropForeign('permissoes_itens_clouds_item_id_foreign');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToItensCloudTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('itens_cloud', function (Blueprint $table) {
            $table->foreign('arquivo_id')->references('id')->on('arquivos')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('cloud_id')->references('id')->on('clouds')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('pertence_anterior')->references('id')->on('itens_cloud')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('pertence')->references('id')->on('itens_cloud')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('quem_aprovou')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('quem_criou')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('quem_editou')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('quem_excluiu')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('quem_moveu')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('quem_revisou')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('itens_cloud', function (Blueprint $table) {
            $table->dropForeign('itens_cloud_arquivo_id_foreign');
            $table->dropForeign('itens_cloud_cloud_id_foreign');
            $table->dropForeign('itens_cloud_pertence_anterior_foreign');
            $table->dropForeign('itens_cloud_pertence_foreign');
            $table->dropForeign('itens_cloud_quem_aprovou_foreign');
            $table->dropForeign('itens_cloud_quem_criou_foreign');
            $table->dropForeign('itens_cloud_quem_editou_foreign');
            $table->dropForeign('itens_cloud_quem_excluiu_foreign');
            $table->dropForeign('itens_cloud_quem_moveu_foreign');
            $table->dropForeign('itens_cloud_quem_revisou_foreign');
        });
    }
}

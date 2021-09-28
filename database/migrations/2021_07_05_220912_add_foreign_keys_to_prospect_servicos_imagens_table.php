<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToProspectServicosImagensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prospect_servicos_imagens', function (Blueprint $table) {
            $table->foreign('arquivo_id')->references('id')->on('arquivos')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('servicos_prospect_id')->references('id')->on('servicos_prospects')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prospect_servicos_imagens', function (Blueprint $table) {
            $table->dropForeign('prospect_servicos_imagens_arquivo_id_foreign');
            $table->dropForeign('prospect_servicos_imagens_servicos_prospect_id_foreign');
        });
    }
}

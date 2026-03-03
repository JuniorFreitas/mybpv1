<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSegmentoTreinamentoIdToCarteiraAssinaturasTable extends Migration
{
    /**
     * Run the migrations.
     * Assinaturas podem ser por segmento: null = padrão da empresa, preenchido = usada na carteira daquele segmento.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('carteira_assinaturas', function (Blueprint $table) {
            $table->unsignedBigInteger('segmento_treinamento_id')->nullable()->after('tipo');
            $table->foreign('segmento_treinamento_id')->references('id')->on('segmentos_treinamento')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('carteira_assinaturas', function (Blueprint $table) {
            $table->dropForeign(['segmento_treinamento_id']);
            $table->dropColumn('segmento_treinamento_id');
        });
    }
}

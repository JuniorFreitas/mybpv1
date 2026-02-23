<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSegmentoTreinamentoIdToVencimentosAndAdmissoes extends Migration
{
    /**
     * Run the migrations.
     * Vencimentos e Admissão vinculados ao segmento (categorização por tipo/segmento).
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vencimentos', function (Blueprint $table) {
            $table->unsignedBigInteger('segmento_treinamento_id')->nullable()->after('empresa_id');
            $table->foreign('segmento_treinamento_id')->references('id')->on('segmentos_treinamento')->onDelete('set null');
        });

        Schema::table('admissoes', function (Blueprint $table) {
            $table->unsignedBigInteger('segmento_treinamento_id')->nullable()->after('status_carteira_treinamento');
            $table->foreign('segmento_treinamento_id')->references('id')->on('segmentos_treinamento')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vencimentos', function (Blueprint $table) {
            $table->dropForeign(['segmento_treinamento_id']);
            $table->dropColumn('segmento_treinamento_id');
        });

        Schema::table('admissoes', function (Blueprint $table) {
            $table->dropForeign(['segmento_treinamento_id']);
            $table->dropColumn('segmento_treinamento_id');
        });
    }
}

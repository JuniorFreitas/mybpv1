<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPadraoTreinamentoIdToAdmissoesAndCarteiraAssinaturas extends Migration
{
    public function up()
    {
        Schema::table('admissoes', function (Blueprint $table) {
            $table->unsignedBigInteger('padrao_treinamento_id')->nullable()->after('segmento_treinamento_id');
            $table->foreign('padrao_treinamento_id')
                ->references('id')->on('padroes_treinamento')->onDelete('set null');
        });

        Schema::table('carteira_assinaturas', function (Blueprint $table) {
            $table->unsignedBigInteger('padrao_treinamento_id')->nullable()->after('segmento_treinamento_id');
            $table->foreign('padrao_treinamento_id')
                ->references('id')->on('padroes_treinamento')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('carteira_assinaturas', function (Blueprint $table) {
            $table->dropForeign(['padrao_treinamento_id']);
            $table->dropColumn('padrao_treinamento_id');
        });

        Schema::table('admissoes', function (Blueprint $table) {
            $table->dropForeign(['padrao_treinamento_id']);
            $table->dropColumn('padrao_treinamento_id');
        });
    }
}

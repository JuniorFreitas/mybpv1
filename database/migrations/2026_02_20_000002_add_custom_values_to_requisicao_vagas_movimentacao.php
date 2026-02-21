<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomValuesToRequisicaoVagasMovimentacao extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requisicao_vagas_movimentacao', function (Blueprint $table) {
            $table->json('custom_values')->nullable()->after('treinamento_excecao');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('requisicao_vagas_movimentacao', function (Blueprint $table) {
            $table->dropColumn('custom_values');
        });
    }
}

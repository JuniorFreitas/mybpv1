<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefinicaoContratoToAvaliacaoNoventaFeedbackQuantidades extends Migration
{
    public function up()
    {
        Schema::table('avaliacao_noventa_feedback_quantidades', function (Blueprint $table) {
            $table->string('definicao_contrato', 20)->nullable()->after('quantidade_avaliacao')
                ->comment('prorroga = prorroga contrato, finaliza = finaliza contrato');
        });
    }

    public function down()
    {
        Schema::table('avaliacao_noventa_feedback_quantidades', function (Blueprint $table) {
            $table->dropColumn('definicao_contrato');
        });
    }
}

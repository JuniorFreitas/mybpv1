<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDetalhesContratacaoToRequisicaoVagasMovimentacao extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requisicao_vagas_movimentacao', function (Blueprint $table) {
            // Colunas de detalhes da contratação (de tipo_contratacoes)
            $table->string('posicao')->nullable()->after('observacao');
            $table->string('processo')->nullable()->after('posicao');
            $table->string('nome_indicacao')->nullable()->after('processo');
            $table->string('contrato')->nullable()->after('nome_indicacao');
            $table->string('local_trabalho')->nullable()->after('contrato');
            $table->string('horario')->nullable()->after('local_trabalho');
            $table->unsignedBigInteger('gestor_id')->nullable()->index()->after('horario');
            $table->string('gestor')->nullable()->after('gestor_id');
            $table->boolean('ppra')->nullable()->after('gestor');
            $table->string('salario')->nullable()->after('ppra');
            $table->decimal('salario_valor', 10, 2)->nullable()->after('salario');
            $table->string('beneficio')->nullable()->after('salario_valor');
            $table->string('beneficio_excecao')->nullable()->after('beneficio');
            $table->string('treinamento')->nullable()->after('beneficio_excecao');
            $table->string('treinamento_excecao')->nullable()->after('treinamento');

            // Foreign key para gestor
            $table->foreign('gestor_id')->references('id')->on('users')->onDelete('set null');
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
            $table->dropForeign(['gestor_id']);
            $table->dropColumn([
                'posicao',
                'processo',
                'nome_indicacao',
                'contrato',
                'local_trabalho',
                'horario',
                'gestor_id',
                'gestor',
                'ppra',
                'salario',
                'salario_valor',
                'beneficio',
                'beneficio_excecao',
                'treinamento',
                'treinamento_excecao'
            ]);
        });
    }
}

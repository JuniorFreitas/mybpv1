<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MigrateDetalhesContratacaoToRequisicaoVagasMovimentacao extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Copiar dados de tipo_contratacoes para requisicao_vagas_movimentacao
        DB::statement("
            UPDATE requisicao_vagas_movimentacao rvm
            INNER JOIN tipo_contratacaos tc ON rvm.id = tc.requisicao_vaga_id
            SET
                rvm.posicao = tc.posicao,
                rvm.processo = tc.processo,
                rvm.nome_indicacao = tc.nome_indicacao,
                rvm.contrato = tc.contrato,
                rvm.local_trabalho = tc.local_trabalho,
                rvm.horario = tc.horario,
                rvm.gestor_id = tc.gestor_id,
                rvm.gestor = tc.gestor,
                rvm.ppra = tc.ppra,
                rvm.salario = tc.salario,
                rvm.salario_valor = tc.salario_valor,
                rvm.beneficio = tc.beneficio,
                rvm.beneficio_excecao = tc.beneficio_excecao,
                rvm.treinamento = tc.treinamento,
                rvm.treinamento_excecao = tc.treinamento_excecao
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Limpar os dados copiados
        DB::statement("
            UPDATE requisicao_vagas_movimentacao
            SET
                posicao = NULL,
                processo = NULL,
                nome_indicacao = NULL,
                contrato = NULL,
                local_trabalho = NULL,
                horario = NULL,
                gestor_id = NULL,
                gestor = NULL,
                ppra = NULL,
                salario = NULL,
                salario_valor = NULL,
                beneficio = NULL,
                beneficio_excecao = NULL,
                treinamento = NULL,
                treinamento_excecao = NULL
        ");
    }
}

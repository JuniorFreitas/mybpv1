<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MigrateRequisicaoVagasDataToMovimentacao extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Copiar dados da tabela antiga para a nova
        // Preservando todos os dados e mapeando para a nova estrutura
        DB::statement("
            INSERT INTO requisicao_vagas_movimentacao (
                id,
                empresa_id,
                cliente_id,
                centro_custo_id,
                cargo_id,
                area_id,
                quantidade,
                tipo_contratacao,
                prioridade,
                imediata,
                previsao_inicio,
                solicitante,
                observacao,
                user_id,
                user_aprovacao_id,
                data_aprovacao,
                obs_aprovacao,
                status_aprovacao,
                aprovacao_extra_id,
                data_aprovacao_extra,
                obs_aprovacao_extra,
                status_aprovacao_extra,
                rh_aprovacao_id,
                data_aprovacao_rh,
                obs_rh,
                status_aprovacao_rh,
                aprovado_via_script,
                created_at,
                updated_at
            )
            SELECT
                rv.id,
                rv.empresa_id,
                rv.cliente_id,
                rv.centro_custo_id,
                rv.cargo_id,
                rv.area_id,
                rv.quantidade,
                rv.tipo_contratacao,
                rv.prioridade,
                rv.imediata,
                rv.previsao_inicio,
                rv.solicitante,
                rv.observacao,
                rv.user_id,
                rv.user_aprovacao_id,
                rv.data_aprovacao,
                rv.obs_aprovacao,
                rv.status_aprovacao,
                rv.aprovacao_extra_id,
                rv.data_aprovacao_extra,
                rv.obs_aprovacao_extra,
                rv.status_aprovacao_extra,
                NULL as rh_aprovacao_id,
                NULL as data_aprovacao_rh,
                NULL as obs_rh,
                NULL as status_aprovacao_rh,
                0 as aprovado_via_script,
                rv.created_at,
                rv.updated_at
            FROM requisicao_vagas rv
            -- WHERE rv.deleted_at IS NULL
        ");

        // Restaurar a sequência do auto_increment para a nova tabela
        $maxId = DB::table('requisicao_vagas_movimentacao')->max('id');
        if ($maxId) {
            DB::statement("ALTER TABLE requisicao_vagas_movimentacao AUTO_INCREMENT = " . ($maxId + 1));
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Limpar dados copiados e restaurar a tabela antiga
        DB::table('requisicao_vagas_movimentacao')->delete();
    }
}

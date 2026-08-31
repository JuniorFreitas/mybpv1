<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Todo o histórico ligado a uma filial (via centro_custo_filial_id, o
     * sistema antigo) ainda estava com empresa_id da MATRIZ — por isso
     * ninguém aparecia em Admissão/Movimentação ao trabalhar como a
     * empresa-filial nova (Fase 5). Reatribui empresa_id pra bater com a
     * empresa real da filial (cliente_filials.empresa_nova_id), em bloco:
     * o próprio Centro de Custo e todos os registros que apontam pra ele,
     * juntos, pra não quebrar a relação CentroCusto quando vista a partir
     * da empresa nova.
     */
    public function up(): void
    {
        // 1) Centro de Custo em si — só os que têm vínculo ativo com uma
        // filial já migrada (empresa_nova_id preenchido).
        DB::statement("
            UPDATE centro_custos cc
            INNER JOIN centro_custo_filials ccf ON ccf.centro_custo_id = cc.id AND ccf.ativo = 1
            INNER JOIN cliente_filials cf ON cf.id = ccf.cliente_filial_id AND cf.empresa_nova_id IS NOT NULL
            SET cc.empresa_id = cf.empresa_nova_id
        ");

        // 2) Tabelas com centro_custo_filial_id + empresa_id próprios.
        // `admissoes` NÃO tem empresa_id (não é TenantTrait) — quem escopa
        // visibilidade é `feedback_curriculos.empresa_id` (via admissoes.feedback_id),
        // tratado no passo 3.
        foreach (['admissoes_previstas', 'demissao_previstas', 'intermitente_fixo_previstas', 'valor_extra_previstas'] as $tabela) {
            DB::statement("
                UPDATE {$tabela} t
                INNER JOIN centro_custo_filials ccf ON ccf.id = t.centro_custo_filial_id
                INNER JOIN cliente_filials cf ON cf.id = ccf.cliente_filial_id AND cf.empresa_nova_id IS NOT NULL
                SET t.empresa_id = cf.empresa_nova_id
                WHERE t.centro_custo_filial_id IS NOT NULL
            ");
        }

        // 3) feedback_curriculos — é quem de fato escopa Admissão/Pós-Admissão
        // (FeedbackCurriculo usa TenantTrait; Admissao não tem empresa_id
        // própria). Resolve via admissoes.feedback_id -> admissoes.centro_custo_filial_id.
        DB::statement("
            UPDATE feedback_curriculos fc
            INNER JOIN admissoes a ON a.feedback_id = fc.id AND a.centro_custo_filial_id IS NOT NULL
            INNER JOIN centro_custo_filials ccf ON ccf.id = a.centro_custo_filial_id
            INNER JOIN cliente_filials cf ON cf.id = ccf.cliente_filial_id AND cf.empresa_nova_id IS NOT NULL
            SET fc.empresa_id = cf.empresa_nova_id
        ");

        // 3) mudanca_cargo tem 2 colunas (antes/depois da troca) — usa o
        // estado NOVO como fonte da verdade; sem novo, cai no anterior.
        DB::statement("
            UPDATE mudanca_cargo m
            INNER JOIN centro_custo_filials ccf ON ccf.id = COALESCE(m.novo_centro_custo_filial_id, m.anterior_centro_custo_filial_id)
            INNER JOIN cliente_filials cf ON cf.id = ccf.cliente_filial_id AND cf.empresa_nova_id IS NOT NULL
            SET m.empresa_id = cf.empresa_nova_id
            WHERE m.novo_centro_custo_filial_id IS NOT NULL OR m.anterior_centro_custo_filial_id IS NOT NULL
        ");
    }

    public function down(): void
    {
        // Não reversível de forma segura (não há como recuperar o
        // empresa_id original da matriz sem perder informação).
    }
};

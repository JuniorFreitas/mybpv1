<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Continuação da 2026_08_30_090000 — mesma realocação de histórico de
     * filial pra empresa nova, agora pras 17 tabelas restantes do sistema
     * que têm empresa_id e são amarradas a uma pessoa (curriculo/feedback/
     * admissão). Levantamento exaustivo via information_schema, não uma
     * lista escolhida à mão — é "pra tudo", como pedido.
     */
    public function up(): void
    {
        // Padrão A — ligadas por admissao_id direto.
        foreach (['admissao_asos', 'admissao_customs', 'ferias', 'ferias_calculo_avos'] as $tabela) {
            DB::statement("
                UPDATE {$tabela} t
                INNER JOIN admissoes a ON a.id = t.admissao_id AND a.centro_custo_filial_id IS NOT NULL
                INNER JOIN centro_custo_filials ccf ON ccf.id = a.centro_custo_filial_id
                INNER JOIN cliente_filials cf ON cf.id = ccf.cliente_filial_id AND cf.empresa_nova_id IS NOT NULL
                SET t.empresa_id = cf.empresa_nova_id
            ");
        }

        // Padrão B — ligadas por feedback_id; feedback_curriculos.empresa_id
        // já é a fonte da verdade (corrigida na migration anterior), só
        // sincroniza a partir dela.
        foreach ([
            'auditoria_internas', 'cihs', 'curriculo_carta_oferta', 'exame_funcionarios',
            'examesesmts', 'intermitentes', 'log_historico', 'recrutamento_historicos',
            'simulado_candidatos', 'treinamento_vencimento_historicos',
        ] as $tabela) {
            DB::statement("
                UPDATE {$tabela} t
                INNER JOIN feedback_curriculos fc ON fc.id = t.feedback_id
                SET t.empresa_id = fc.empresa_id
                WHERE t.feedback_id IS NOT NULL
            ");
        }

        // Padrão C — só têm curriculo_id (sem feedback_id/admissao_id
        // próprio). Usa o feedback mais antigo desse currículo como
        // referência quando ambíguo (candidato com mais de 1 processo).
        foreach (['curriculo_vaga_empresa', 'emails_pre_admissao', 'parabens_enviados'] as $tabela) {
            DB::statement("
                UPDATE {$tabela} t
                INNER JOIN (
                    SELECT curriculo_id, MIN(empresa_id) AS empresa_id
                    FROM feedback_curriculos
                    GROUP BY curriculo_id
                ) fc ON fc.curriculo_id = t.curriculo_id
                SET t.empresa_id = fc.empresa_id
            ");
        }
    }

    public function down(): void
    {
        // Não reversível de forma segura (mesmo motivo da migration anterior).
    }
};

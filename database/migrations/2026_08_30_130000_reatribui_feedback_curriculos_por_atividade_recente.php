<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fecha a lacuna que sobrou depois da migration 090100: 1.027
     * feedback_curriculos cujo empresa_id não bate com os CIHs da própria
     * pessoa (a admissão original não tinha centro_custo_filial_id, mas a
     * pessoa tem atividade real na empresa nova). Resolve pela empresa do
     * CIH mais recente (data_lancamento) — pra quem só tem CIH numa empresa
     * isso é direto; pra quem tem nas duas, assume que a atividade mais
     * recente reflete onde a pessoa está hoje.
     *
     * Depois disso, resincroniza as 9 tabelas que espelham
     * feedback_curriculos.empresa_id (mesmo padrão da migration 090100).
     */
    public function up(): void
    {
        DB::statement("
            UPDATE feedback_curriculos fc
            INNER JOIN (
                SELECT cfb.feedback_id, c.empresa_id
                FROM cih_feedback cfb
                INNER JOIN cihs c ON c.id = cfb.cih_id
                INNER JOIN (
                    SELECT cfb2.feedback_id, MAX(c2.data_lancamento) AS max_data
                    FROM cih_feedback cfb2
                    INNER JOIN cihs c2 ON c2.id = cfb2.cih_id
                    GROUP BY cfb2.feedback_id
                ) recente ON recente.feedback_id = cfb.feedback_id AND c.data_lancamento = recente.max_data
                GROUP BY cfb.feedback_id, c.empresa_id
            ) alvo ON alvo.feedback_id = fc.id
            SET fc.empresa_id = alvo.empresa_id
            WHERE fc.empresa_id != alvo.empresa_id
        ");

        foreach ([
            'auditoria_internas', 'cihs', 'curriculo_carta_oferta', 'exame_funcionarios',
            'examesesmts', 'intermitentes', 'log_historico', 'recrutamento_historicos',
            'simulado_candidatos', 'treinamento_vencimento_historicos',
        ] as $tabela) {
            DB::statement("
                UPDATE {$tabela} t
                INNER JOIN feedback_curriculos fc ON fc.id = t.feedback_id
                SET t.empresa_id = fc.empresa_id
                WHERE t.feedback_id IS NOT NULL AND t.empresa_id != fc.empresa_id
            ");
        }
    }

    public function down(): void
    {
        // Não reversível de forma segura.
    }
};

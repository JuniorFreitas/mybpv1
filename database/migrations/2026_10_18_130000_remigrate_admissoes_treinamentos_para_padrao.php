<?php

use App\Services\Treinamento\MigrarSegmentosParaPadroesTreinamento;
use Illuminate\Database\Migrations\Migration;

/**
 * Completa o backfill de padrao_treinamento_id em admissões/assinaturas
 * e garante pivots de vencimento — inclusive admissões sem segmento legado.
 * Idempotente: seguro reexecutar o serviço.
 */
class RemigrateAdmissoesTreinamentosParaPadrao extends Migration
{
    public function up()
    {
        (new MigrarSegmentosParaPadroesTreinamento())->executar();
    }

    public function down()
    {
        // Dados migrados não são revertidos automaticamente.
    }
}

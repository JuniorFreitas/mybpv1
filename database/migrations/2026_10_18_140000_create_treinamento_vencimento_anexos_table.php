<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Anexos FAT por treinamento/vencimento (até 5). Mantém treinamento_vencimento.arquivo_id
 * como o primeiro anexo para compatibilidade (ícone na listagem, importação, etc.).
 */
class CreateTreinamentoVencimentoAnexosTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('treinamento_vencimento_anexos')) {
            Schema::create('treinamento_vencimento_anexos', function (Blueprint $table) {
                $table->unsignedInteger('treinamento_id');
                $table->unsignedInteger('vencimento_id');
                $table->unsignedInteger('arquivo_id');
                $table->unsignedTinyInteger('ordem')->default(0);

                $table->primary(['treinamento_id', 'vencimento_id', 'arquivo_id'], 'tva_primary');
                $table->index(['treinamento_id', 'vencimento_id'], 'tva_tv_idx');

                $table->foreign('treinamento_id', 'tva_treinamento_fk')
                    ->references('id')->on('treinamentos')->onDelete('CASCADE');
                $table->foreign('vencimento_id', 'tva_vencimento_fk')
                    ->references('id')->on('vencimentos')->onDelete('CASCADE');
                $table->foreign('arquivo_id', 'tva_arquivo_fk')
                    ->references('id')->on('arquivos')->onDelete('CASCADE');
            });
        }

        // Backfill: arquivo_id legado → primeiro anexo (set-based; idempotente via PK)
        if (Schema::hasTable('treinamento_vencimento') && Schema::hasColumn('treinamento_vencimento', 'arquivo_id')) {
            DB::statement('
                INSERT IGNORE INTO treinamento_vencimento_anexos
                    (treinamento_id, vencimento_id, arquivo_id, ordem)
                SELECT treinamento_id, vencimento_id, arquivo_id, 0
                FROM treinamento_vencimento
                WHERE arquivo_id IS NOT NULL
            ');
        }
    }

    public function down()
    {
        Schema::dropIfExists('treinamento_vencimento_anexos');
    }
}

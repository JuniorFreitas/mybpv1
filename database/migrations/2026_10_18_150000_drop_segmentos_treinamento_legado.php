<?php

use App\Services\Treinamento\MigrarSegmentosParaPadroesTreinamento;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Backfill final (idempotente) + remove o legado SegmentoTreinamento.
 * Seguro no deploy padrão: só `php artisan migrate` (sem comando artisan extra).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Garante padrao_treinamento_id / pivots ANTES de dropar as colunas/tabelas.
        // Idempotente: se 120003/130000 já rodaram, só completa o que faltar.
        if (Schema::hasTable('segmentos_treinamento') && Schema::hasTable('padroes_treinamento')) {
            (new MigrarSegmentosParaPadroesTreinamento())->executar();
        }

        $this->dropSegmentoColumn('vencimentos');
        $this->dropSegmentoColumn('admissoes');
        $this->dropSegmentoColumn('carteira_assinaturas');

        Schema::dropIfExists('cliente_segmento_treinamento');
        Schema::dropIfExists('segmentos_treinamento');
    }

    private function dropSegmentoColumn(string $table): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'segmento_treinamento_id')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            $fk = DB::selectOne(
                'SELECT CONSTRAINT_NAME as name
                 FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = ?
                   AND COLUMN_NAME = ?
                   AND REFERENCED_TABLE_NAME IS NOT NULL
                 LIMIT 1',
                [$table, 'segmento_treinamento_id']
            );
            if ($fk && !empty($fk->name)) {
                DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fk->name}`");
            }
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn('segmento_treinamento_id');
            });

            return;
        }

        Schema::table($table, function (Blueprint $blueprint) {
            try {
                $blueprint->dropForeign(['segmento_treinamento_id']);
            } catch (\Throwable $e) {
                // SQLite / ambientes sem FK nomeada padrão
            }
            $blueprint->dropColumn('segmento_treinamento_id');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('segmentos_treinamento')) {
            Schema::create('segmentos_treinamento', function (Blueprint $table) {
                $table->id();
                $table->string('nome');
                $table->string('slug', 50)->unique();
                $table->boolean('ativo')->default(true);
                $table->json('config_carteira')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('cliente_segmento_treinamento')) {
            Schema::create('cliente_segmento_treinamento', function (Blueprint $table) {
                $table->unsignedBigInteger('cliente_id');
                $table->unsignedBigInteger('segmento_treinamento_id');
                $table->primary(['cliente_id', 'segmento_treinamento_id'], 'cliente_seg_treinamento_primary');
                $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
                $table->foreign('segmento_treinamento_id')->references('id')->on('segmentos_treinamento')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('vencimentos') && !Schema::hasColumn('vencimentos', 'segmento_treinamento_id')) {
            Schema::table('vencimentos', function (Blueprint $table) {
                $table->unsignedBigInteger('segmento_treinamento_id')->nullable()->after('empresa_id');
                $table->foreign('segmento_treinamento_id')->references('id')->on('segmentos_treinamento')->onDelete('set null');
            });
        }

        if (Schema::hasTable('admissoes') && !Schema::hasColumn('admissoes', 'segmento_treinamento_id')) {
            Schema::table('admissoes', function (Blueprint $table) {
                $table->unsignedBigInteger('segmento_treinamento_id')->nullable()->after('status_carteira_treinamento');
                $table->foreign('segmento_treinamento_id')->references('id')->on('segmentos_treinamento')->onDelete('set null');
            });
        }

        if (Schema::hasTable('carteira_assinaturas') && !Schema::hasColumn('carteira_assinaturas', 'segmento_treinamento_id')) {
            Schema::table('carteira_assinaturas', function (Blueprint $table) {
                $table->unsignedBigInteger('segmento_treinamento_id')->nullable()->after('tipo');
                $table->foreign('segmento_treinamento_id')->references('id')->on('segmentos_treinamento')->onUpdate('cascade')->onDelete('set null');
            });
        }
    }
};

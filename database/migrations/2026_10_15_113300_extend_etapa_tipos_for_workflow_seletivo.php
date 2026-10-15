<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('etapa_tipos', function (Blueprint $table) {
            if (!Schema::hasColumn('etapa_tipos', 'vaga_id')) {
                // etapa_tipos.id e vagas.id são int unsigned neste banco
                $table->unsignedInteger('vaga_id')->nullable()->after('cliente_id')->index();
            }
            if (!Schema::hasColumn('etapa_tipos', 'ordem')) {
                $table->unsignedInteger('ordem')->default(0)->after('nome');
            }
            if (!Schema::hasColumn('etapa_tipos', 'chave')) {
                $table->string('chave', 80)->nullable()->after('ordem');
            }
            if (!Schema::hasColumn('etapa_tipos', 'tipo')) {
                $table->string('tipo', 30)->default('avanco')->after('chave');
            }
            if (!Schema::hasColumn('etapa_tipos', 'obrigatoria')) {
                $table->boolean('obrigatoria')->default(false)->after('tipo');
            }
            if (!Schema::hasColumn('etapa_tipos', 'depende_de_id')) {
                $table->unsignedInteger('depende_de_id')->nullable()->after('obrigatoria');
            }
            if (!Schema::hasColumn('etapa_tipos', 'is_apto_admissao')) {
                $table->boolean('is_apto_admissao')->default(false)->after('depende_de_id');
            }
            if (!Schema::hasColumn('etapa_tipos', 'permite_email')) {
                $table->boolean('permite_email')->default(true)->after('is_apto_admissao');
            }
            if (!Schema::hasColumn('etapa_tipos', 'habilidade')) {
                $table->string('habilidade', 120)->nullable()->after('permite_email');
            }
        });

        // Corrige tipos caso colunas tenham sido criadas como bigint em tentativa anterior
        $this->alinharTiposFk();

        Schema::table('etapa_tipos', function (Blueprint $table) {
            if (Schema::hasColumn('etapa_tipos', 'depende_de_id') && !$this->fkExiste('etapa_tipos', 'etapa_tipos_depende_de_id_foreign')) {
                try {
                    $table->foreign('depende_de_id', 'etapa_tipos_depende_de_id_foreign')
                        ->references('id')
                        ->on('etapa_tipos')
                        ->onUpdate('RESTRICT')
                        ->onDelete('SET NULL');
                } catch (\Throwable $e) {
                    // FK pode já existir em ambientes parcialmente migrados
                }
            }
            if (Schema::hasColumn('etapa_tipos', 'vaga_id') && !$this->fkExiste('etapa_tipos', 'etapa_tipos_vaga_id_foreign')) {
                try {
                    $table->foreign('vaga_id', 'etapa_tipos_vaga_id_foreign')
                        ->references('id')
                        ->on('vagas')
                        ->onUpdate('RESTRICT')
                        ->onDelete('CASCADE');
                } catch (\Throwable $e) {
                    // ignore
                }
            }
        });

        // Índice único lógico: empresa + cargo (null=padrão) + chave
        if (!$this->indiceExiste('etapa_tipos', 'etapa_tipos_cliente_vaga_chave_unique')) {
            try {
                Schema::table('etapa_tipos', function (Blueprint $table) {
                    $table->unique(['cliente_id', 'vaga_id', 'chave'], 'etapa_tipos_cliente_vaga_chave_unique');
                });
            } catch (\Throwable $e) {
                // ignore se já existir
            }
        }

        // Habilidade de configuração
        $existe = DB::table('habilidades')->where('nome', 'administracao_etapas_seletivo')->exists();
        if (!$existe) {
            DB::table('habilidades')->insert([
                'nome' => 'administracao_etapas_seletivo',
                'descricao' => 'Gerenciar workflow/etapas do seletivo',
            ]);
        }

        $habilidadeId = DB::table('habilidades')->where('nome', 'administracao_etapas_seletivo')->value('id');
        if ($habilidadeId) {
            $masters = DB::table('papeis')->where('master', true)->where('ativo', true)->pluck('id');
            foreach ($masters as $papelId) {
                $jaTem = DB::table('papeis_habilidades')
                    ->where('papel_id', $papelId)
                    ->where('habilidade_id', $habilidadeId)
                    ->exists();
                if (!$jaTem) {
                    DB::table('papeis_habilidades')->insert([
                        'papel_id' => $papelId,
                        'habilidade_id' => $habilidadeId,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('etapa_tipos', function (Blueprint $table) {
            try {
                $table->dropUnique('etapa_tipos_cliente_vaga_chave_unique');
            } catch (\Throwable $e) {
            }
            try {
                $table->dropForeign('etapa_tipos_depende_de_id_foreign');
            } catch (\Throwable $e) {
            }
            try {
                $table->dropForeign('etapa_tipos_vaga_id_foreign');
            } catch (\Throwable $e) {
            }

            foreach ([
                'vaga_id', 'ordem', 'chave', 'tipo', 'obrigatoria', 'depende_de_id',
                'is_apto_admissao', 'permite_email', 'habilidade',
            ] as $col) {
                if (Schema::hasColumn('etapa_tipos', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    private function alinharTiposFk(): void
    {
        $cols = collect(DB::select('SHOW COLUMNS FROM etapa_tipos'))->keyBy('Field');

        if (isset($cols['depende_de_id']) && stripos((string) $cols['depende_de_id']->Type, 'bigint') !== false) {
            DB::statement('ALTER TABLE etapa_tipos MODIFY depende_de_id INT UNSIGNED NULL');
        }

        if (isset($cols['vaga_id']) && stripos((string) $cols['vaga_id']->Type, 'bigint') !== false) {
            DB::statement('ALTER TABLE etapa_tipos MODIFY vaga_id INT UNSIGNED NULL');
        }
    }

    private function fkExiste(string $table, string $name): bool
    {
        $row = DB::selectOne(
            'SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = ?',
            [$table, $name, 'FOREIGN KEY']
        );

        return (bool) $row;
    }

    private function indiceExiste(string $table, string $name): bool
    {
        $row = DB::selectOne(
            'SELECT INDEX_NAME FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1',
            [$table, $name]
        );

        return (bool) $row;
    }
};

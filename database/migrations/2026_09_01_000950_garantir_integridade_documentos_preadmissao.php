<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const DOCUMENTO_INDEX = 'doc_adm_empresa_tipo_unique';
    private const CATEGORIA_INDEX = 'doc_adm_cat_empresa_label_unique';

    public function up(): void
    {
        $this->garantirIntegridadeDocumentos();
        $this->garantirIntegridadeCategorias();
    }

    public function down(): void
    {
        if (Schema::hasTable('documentos_curriculos_adm_empresa')) {
            Schema::table('documentos_curriculos_adm_empresa', function (Blueprint $table) {
                $table->dropUnique(self::DOCUMENTO_INDEX);
            });
        }

        if (Schema::hasTable('documentos_curriculos_cat_adm_empresa')) {
            Schema::table('documentos_curriculos_cat_adm_empresa', function (Blueprint $table) {
                $table->dropUnique(self::CATEGORIA_INDEX);
            });
        }
    }

    private function garantirIntegridadeDocumentos(): void
    {
        if (!Schema::hasTable('documentos_curriculos_adm_empresa')) {
            return;
        }

        $duplicado = DB::table('documentos_curriculos_adm_empresa')
            ->select('empresa_id', 'tipo')
            ->groupBy('empresa_id', 'tipo')
            ->havingRaw('COUNT(*) > 1')
            ->first();

        if ($duplicado) {
            throw new RuntimeException(
                'Existem tipos de documentos da pré-admissão duplicados na empresa '
                . $duplicado->empresa_id . '. Corrija os dados antes de executar esta migration.'
            );
        }

        DB::table('documentos_curriculos_adm_empresa')
            ->select('id', 'empresa_id', 'tipo')
            ->orderBy('id')
            ->chunkById(500, function ($documentos) {
                foreach ($documentos as $documento) {
                    $normalizado = strtolower((string) preg_replace('/[^a-zA-Z0-9]+/', '', \Illuminate\Support\Str::ascii((string) $documento->tipo)));
                    if ($normalizado === 'foto3x4' && $documento->tipo !== 'foto3x4') {
                        throw new RuntimeException(
                            'Existe uma variação legada do tipo foto3x4 na empresa '
                            . $documento->empresa_id . '. Corrija o tipo antes de executar esta migration.'
                        );
                    }
                }
            }, 'id');

        Schema::table('documentos_curriculos_adm_empresa', function (Blueprint $table) {
            $table->unique(['empresa_id', 'tipo'], self::DOCUMENTO_INDEX);
        });
    }

    private function garantirIntegridadeCategorias(): void
    {
        if (!Schema::hasTable('documentos_curriculos_cat_adm_empresa')) {
            return;
        }

        $duplicada = DB::table('documentos_curriculos_cat_adm_empresa')
            ->select('empresa_id', DB::raw('LOWER(label) as label_normalizada'))
            ->groupBy('empresa_id', DB::raw('LOWER(label)'))
            ->havingRaw('COUNT(*) > 1')
            ->first();

        if ($duplicada) {
            throw new RuntimeException(
                'Existem categorias de documentos da pré-admissão duplicadas na empresa '
                . $duplicada->empresa_id . '. Corrija os dados antes de executar esta migration.'
            );
        }

        Schema::table('documentos_curriculos_cat_adm_empresa', function (Blueprint $table) {
            $table->unique(['empresa_id', 'label'], self::CATEGORIA_INDEX);
        });
    }
};

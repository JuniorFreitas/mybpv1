<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Índice em examesesmts.exame_funcionario_id: o resultado do ASO é sempre buscado pelo encaminhamento
 * (controle de exames, ficha, conformidade). Sem ele, "encaminhamento ainda sem resultado" faz varredura completa.
 */
return new class extends Migration
{
    private const INDICE = 'examesesmts_exame_funcionario_id_index';

    public function up(): void
    {
        if ($this->temIndice()) {
            return;
        }

        Schema::table('examesesmts', function (Blueprint $table) {
            $table->index('exame_funcionario_id', self::INDICE);
        });
    }

    public function down(): void
    {
        if (!$this->temIndice()) {
            return;
        }

        Schema::table('examesesmts', function (Blueprint $table) {
            $table->dropIndex(self::INDICE);
        });
    }

    private function temIndice(): bool
    {
        if (DB::getDriverName() !== 'mysql') {
            return collect(Schema::getIndexes('examesesmts'))->contains(fn ($i) => $i['name'] === self::INDICE);
        }

        return collect(DB::select('SHOW INDEX FROM examesesmts'))
            ->contains(fn ($i) => $i->Column_name === 'exame_funcionario_id');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('curriculo_carta_oferta', function (Blueprint $table) {
            if (!Schema::hasColumn('curriculo_carta_oferta', 'setor')) {
                $table->string('setor', 150)->nullable()->after('local');
            }
            if (!Schema::hasColumn('curriculo_carta_oferta', 'salario')) {
                $table->string('salario', 80)->nullable()->after('setor');
            }
            if (!Schema::hasColumn('curriculo_carta_oferta', 'data_inicio')) {
                $table->date('data_inicio')->nullable()->after('salario');
            }
            if (!Schema::hasColumn('curriculo_carta_oferta', 'public_id')) {
                $table->uuid('public_id')->nullable()->after('token');
            }
            if (!Schema::hasColumn('curriculo_carta_oferta', 'acesso_codigo_hash')) {
                $table->string('acesso_codigo_hash', 255)->nullable()->after('public_id');
            }
            if (!Schema::hasColumn('curriculo_carta_oferta', 'acesso_codigo_expira_em')) {
                $table->timestamp('acesso_codigo_expira_em')->nullable()->after('acesso_codigo_hash');
            }
            if (!Schema::hasColumn('curriculo_carta_oferta', 'acesso_codigo_usado_em')) {
                $table->timestamp('acesso_codigo_usado_em')->nullable()->after('acesso_codigo_expira_em');
            }
        });

        if (Schema::hasColumn('curriculo_carta_oferta', 'public_id')) {
            DB::table('curriculo_carta_oferta')
                ->whereNull('public_id')
                ->orderBy('id')
                ->chunkById(200, function ($rows) {
                    foreach ($rows as $row) {
                        $publicId = !empty($row->token) ? $row->token : (string) Str::uuid();
                        DB::table('curriculo_carta_oferta')
                            ->where('id', $row->id)
                            ->update(['public_id' => $publicId]);
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::table('curriculo_carta_oferta', function (Blueprint $table) {
            foreach ([
                'acesso_codigo_usado_em',
                'acesso_codigo_expira_em',
                'acesso_codigo_hash',
                'public_id',
                'data_inicio',
                'salario',
                'setor',
            ] as $col) {
                if (Schema::hasColumn('curriculo_carta_oferta', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

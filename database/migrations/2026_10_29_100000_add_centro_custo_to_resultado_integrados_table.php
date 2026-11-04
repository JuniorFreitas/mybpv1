<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resultado_integrados', function (Blueprint $table) {
            if (!Schema::hasColumn('resultado_integrados', 'centro_custo_id')) {
                $table->unsignedBigInteger('centro_custo_id')->nullable()->index();
            }
            if (!Schema::hasColumn('resultado_integrados', 'filial')) {
                $table->boolean('filial')->default(false);
            }
            if (!Schema::hasColumn('resultado_integrados', 'centro_custo_filial_id')) {
                $table->unsignedBigInteger('centro_custo_filial_id')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('resultado_integrados', function (Blueprint $table) {
            foreach (['centro_custo_filial_id', 'filial', 'centro_custo_id'] as $coluna) {
                if (Schema::hasColumn('resultado_integrados', $coluna)) {
                    $table->dropColumn($coluna);
                }
            }
        });
    }
};

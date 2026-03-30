<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('avaliacoes', function (Blueprint $table) {
            $table->boolean('mostrar_notas_avaliador_final')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('avaliacoes', function (Blueprint $table) {
            $table->dropColumn('mostrar_notas_avaliador_final');
        });
    }
};


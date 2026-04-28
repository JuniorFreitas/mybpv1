<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vagas', function (Blueprint $table) {
            $table->foreignId('cbo_id')
                ->nullable()
                ->after('empresa_id')
                ->constrained('cbos')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vagas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cbo_id');
        });
    }
};

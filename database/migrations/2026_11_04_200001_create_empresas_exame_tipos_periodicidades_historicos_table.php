<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresas_exame_tipos_periodicidades_historicos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('user_id');
            $table->string('modo_aplicacao', 20); // retroativo|futuro|a_partir_de
            $table->date('data_aplicacao')->nullable();
            $table->boolean('job_despachado')->default(false);
            $table->json('alteracoes');
            $table->string('descricao', 500);
            $table->timestamps();

            $table->index('empresa_id');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresas_exame_tipos_periodicidades_historicos');
    }
};

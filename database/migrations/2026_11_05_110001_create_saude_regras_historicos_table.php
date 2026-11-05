<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saude_regras_historicos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('saude_regra_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('acao', 20);
            $table->string('modo_aplicacao', 20)->nullable();
            $table->date('data_aplicacao')->nullable();
            $table->boolean('job_despachado')->default(false);
            $table->json('antes')->nullable();
            $table->json('depois')->nullable();
            $table->string('descricao', 500);
            $table->timestamps();

            $table->index(['empresa_id', 'id']);
            $table->index('saude_regra_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saude_regras_historicos');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('centro_custo_gestores', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('centro_custo_id');
            $table->unsignedBigInteger('usuario_id');
            $table->string('tipo', 30);
            $table->boolean('ativo')->default(true);
            $table->date('inicio_vigencia')->nullable();
            $table->date('fim_vigencia')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->timestamps();

            $table->foreign('centro_custo_id')->references('id')->on('centro_custos')->cascadeOnDelete();
            $table->foreign('usuario_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('empresa_id')->references('id')->on('users')->cascadeOnDelete();

            $table->index(['centro_custo_id', 'tipo', 'ativo'], 'idx_cc_gestores_cc_tipo_ativo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('centro_custo_gestores');
    }
};

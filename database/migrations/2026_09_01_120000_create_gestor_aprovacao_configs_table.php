<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gestor_aprovacao_configs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('empresa_id');
            $table->string('tipo_processo', 50);
            $table->unsignedBigInteger('gestor_aprovacao_id');
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('gestor_aprovacao_id')->references('id')->on('users')->cascadeOnDelete();

            $table->unique(['empresa_id', 'tipo_processo'], 'uk_gestor_aprovacao_config_empresa_tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gestor_aprovacao_configs');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cbo_familias', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('titulo', 255)->nullable();
            $table->text('descricao_sumaria')->nullable();
            $table->string('fonte', 150)->default('Ministério do Trabalho e Emprego - CBO');
            $table->boolean('ativo')->default(true);
            $table->timestamp('data_importacao')->nullable();
            $table->timestamps();

            $table->index('titulo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cbo_familias');
    }
};

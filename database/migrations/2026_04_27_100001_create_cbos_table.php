<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cbos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('titulo', 255);
            $table->string('codigo_familia', 20)->nullable();
            $table->string('fonte', 150)->default('Ministério do Trabalho e Emprego - CBO');
            $table->boolean('ativo')->default(true);
            $table->timestamp('data_importacao')->nullable();
            $table->timestamps();

            $table->index('titulo');
            $table->index('codigo_familia');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cbos');
    }
};

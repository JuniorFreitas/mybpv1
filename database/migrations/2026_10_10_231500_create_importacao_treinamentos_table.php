<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('importacao_treinamentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->uuid('uuid')->unique();
            $table->string('status', 20)->default('processando');
            $table->unsignedInteger('total')->default(0);
            $table->unsignedInteger('sucesso')->default(0);
            $table->unsignedInteger('erros')->default(0);
            $table->string('arquivo_sucesso')->nullable();
            $table->string('arquivo_erro')->nullable();
            $table->string('mensagem_erro', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('importacao_treinamentos');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresa_whatsapp_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->unique();
            $table->string('nome_exibicao')->nullable();
            $table->string('telefone_contato', 30)->nullable();
            $table->text('endereco_completo')->nullable();
            $table->text('texto_assinatura')->nullable();
            $table->boolean('incluir_rodape_mybp')->default(true);
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('users');
        });

        Schema::create('empresa_whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('tipo_mensagem', 80);
            $table->text('corpo');
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->unique(['empresa_id', 'tipo_mensagem']);
            $table->index(['empresa_id', 'tipo_mensagem', 'ativo']);
            $table->foreign('empresa_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa_whatsapp_templates');
        Schema::dropIfExists('empresa_whatsapp_configs');
    }
};

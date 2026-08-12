<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ata_reuniao_comentarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('ata_reuniao_id');
            $table->unsignedBigInteger('ata_reuniao_acao_id')->nullable();
            $table->unsignedBigInteger('autor_id');
            $table->text('texto');
            $table->json('mencoes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'ata_reuniao_id', 'created_at'], 'ata_coment_emp_ata_data_idx');
            $table->index(['empresa_id', 'ata_reuniao_acao_id', 'created_at'], 'ata_coment_emp_acao_data_idx');
        });

        Schema::create('ata_reuniao_anexos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('ata_reuniao_id');
            $table->unsignedBigInteger('ata_reuniao_acao_id')->nullable();
            $table->unsignedBigInteger('arquivo_id')->nullable();
            $table->unsignedBigInteger('usuario_id');
            $table->string('nome');
            $table->string('tipo')->nullable();
            $table->unsignedBigInteger('tamanho')->nullable();
            $table->string('link', 1000)->nullable();
            $table->string('secao', 60)->default('ata');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'ata_reuniao_id', 'created_at'], 'ata_anexo_emp_ata_data_idx');
            $table->index(['empresa_id', 'ata_reuniao_acao_id', 'created_at'], 'ata_anexo_emp_acao_data_idx');
        });

        Schema::create('ata_reuniao_ciencias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('ata_reuniao_id');
            $table->unsignedBigInteger('user_id');
            $table->string('tipo', 40)->default('ciencia');
            $table->string('ip', 80)->nullable();
            $table->text('comentario')->nullable();
            $table->timestamp('confirmado_em');
            $table->timestamps();

            $table->unique(['empresa_id', 'ata_reuniao_id', 'user_id', 'tipo'], 'ata_ciencia_unique');
            $table->index(['empresa_id', 'user_id', 'confirmado_em'], 'ata_ciencia_emp_user_data_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ata_reuniao_ciencias');
        Schema::dropIfExists('ata_reuniao_anexos');
        Schema::dropIfExists('ata_reuniao_comentarios');
    }
};

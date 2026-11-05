<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Documentos SST (Saúde Ocupacional): repositório versionado de PCMSO, PGR, laudos, ASOs, ordens de serviço etc.
 * saude_documentos → saude_documentos_versoes (arquivo + vigência) → saude_documentos_acessos (trilha).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saude_documentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('tipo', 30);
            $table->string('titulo', 200);
            $table->string('sigilo', 10)->default('comum');
            $table->unsignedBigInteger('feedback_id')->nullable();
            $table->string('origem_tipo', 30)->nullable();
            $table->unsignedBigInteger('origem_id')->nullable();
            $table->string('responsavel', 150)->nullable();
            $table->string('observacao', 1000)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'tipo']);
            $table->index('feedback_id');
            $table->index(['origem_tipo', 'origem_id']);
        });

        Schema::create('saude_documentos_versoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('saude_documento_id');
            $table->unsignedInteger('versao');
            $table->unsignedBigInteger('arquivo_id');
            $table->string('hash_sha256', 64)->nullable();
            $table->date('emissao')->nullable();
            $table->date('vigencia_inicio')->nullable();
            $table->date('vigencia_fim')->nullable();
            $table->unsignedBigInteger('documento_assinatura_id')->nullable();
            $table->unsignedBigInteger('enviado_por')->nullable();
            $table->timestamps();

            $table->unique(['saude_documento_id', 'versao']);
            $table->index('vigencia_fim');
        });

        Schema::create('saude_documentos_acessos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('saude_documento_versao_id');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('user_id');
            $table->string('acao', 15);
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['saude_documento_versao_id', 'created_at'], 'saude_doc_acessos_versao_data_idx');
            $table->index('empresa_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saude_documentos_acessos');
        Schema::dropIfExists('saude_documentos_versoes');
        Schema::dropIfExists('saude_documentos');
    }
};

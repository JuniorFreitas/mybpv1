<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentoParaAssinaturaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documento_para_assinatura', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('tipo_documento', 80);
            $table->string('documentable_type');
            $table->unsignedBigInteger('documentable_id');
            $table->unsignedBigInteger('arquivo_id')->nullable();
            $table->string('hash_sha256', 64)->nullable();
            $table->string('status', 30)->default('rascunho');
            $table->dateTime('data_expiracao')->nullable();
            $table->unsignedBigInteger('solicitante_id')->nullable();
            $table->string('ordem_assinatura', 20)->default('sequencial');
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('clientes')->onDelete('cascade');
            // FK arquivo_id omitida: arquivos.id pode ter tipo incompatível (ex.: INT vs BIGINT) em alguns ambientes
            $table->index('arquivo_id');
            $table->foreign('solicitante_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['documentable_type', 'documentable_id'], 'doc_assinatura_doc_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documento_para_assinatura');
    }
}

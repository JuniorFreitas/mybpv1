<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndicesToDocumentoAssinaturaTables extends Migration
{
    public function up()
    {
        Schema::table('documento_para_assinatura', function (Blueprint $table) {
            $table->index(['empresa_id', 'created_at'], 'doc_assinatura_emp_created_idx');
            $table->index(['empresa_id', 'status', 'created_at'], 'doc_assinatura_emp_status_created_idx');
            $table->index(['empresa_id', 'tipo_documento', 'created_at'], 'doc_assinatura_emp_tipo_created_idx');
            $table->index(['empresa_id', 'solicitante_id'], 'doc_assinatura_emp_solicitante_idx');
        });

        // "token" ja possui indice unique; remover indice simples redundante.
        try {
            Schema::table('documento_assinatura_signatarios', function (Blueprint $table) {
                $table->dropIndex('documento_assinatura_signatarios_token_index');
            });
        } catch (\Throwable $e) {
            // Mantem compatibilidade com ambientes onde o indice nao exista.
        }
    }

    public function down()
    {
        Schema::table('documento_para_assinatura', function (Blueprint $table) {
            $table->dropIndex('doc_assinatura_emp_created_idx');
            $table->dropIndex('doc_assinatura_emp_status_created_idx');
            $table->dropIndex('doc_assinatura_emp_tipo_created_idx');
            $table->dropIndex('doc_assinatura_emp_solicitante_idx');
        });

        Schema::table('documento_assinatura_signatarios', function (Blueprint $table) {
            $table->index('token');
        });
    }
}

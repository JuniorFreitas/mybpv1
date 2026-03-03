<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConsentimentoToDocumentoParaAssinatura extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('documento_para_assinatura', function (Blueprint $table) {
            $table->dateTime('consentimento_ultimo_em')->nullable()->after('ordem_assinatura');
            $table->unsignedBigInteger('consentimento_ultimo_signatario_id')->nullable()->after('consentimento_ultimo_em');
            $table->index('consentimento_ultimo_signatario_id', 'doc_assinatura_consent_signatario_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('documento_para_assinatura', function (Blueprint $table) {
            $table->dropIndex('doc_assinatura_consent_signatario_idx');
            $table->dropColumn(['consentimento_ultimo_em', 'consentimento_ultimo_signatario_id']);
        });
    }
}

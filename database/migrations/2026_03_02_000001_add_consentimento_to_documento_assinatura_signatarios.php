<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConsentimentoToDocumentoAssinaturaSignatarios extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('documento_assinatura_signatarios', function (Blueprint $table) {
            $table->boolean('consentimento_assinatura')->default(false)->after('recusa_motivo');
            $table->dateTime('consentimento_em')->nullable()->after('consentimento_assinatura');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('documento_assinatura_signatarios', function (Blueprint $table) {
            $table->dropColumn(['consentimento_assinatura', 'consentimento_em']);
        });
    }
}

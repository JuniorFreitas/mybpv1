<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddArquivoAssinadoIdToDocumentoParaAssinatura extends Migration
{
    public function up()
    {
        Schema::table('documento_para_assinatura', function (Blueprint $table) {
            $table->unsignedBigInteger('arquivo_assinado_id')->nullable()->after('arquivo_id');
            $table->index('arquivo_assinado_id');
        });
    }

    public function down()
    {
        Schema::table('documento_para_assinatura', function (Blueprint $table) {
            $table->dropIndex(['arquivo_assinado_id']);
            $table->dropColumn('arquivo_assinado_id');
        });
    }
}

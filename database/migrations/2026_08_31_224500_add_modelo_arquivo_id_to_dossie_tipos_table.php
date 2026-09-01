<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddModeloArquivoIdToDossieTiposTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('dossie_tipos') || Schema::hasColumn('dossie_tipos', 'modelo_arquivo_id')) {
            return;
        }

        Schema::table('dossie_tipos', function (Blueprint $table) {
            $table->unsignedBigInteger('modelo_arquivo_id')->nullable()->after('permite_assinatura');
            $table->index('modelo_arquivo_id', 'dossie_tipos_modelo_arquivo_id_idx');
        });
    }

    public function down()
    {
        if (!Schema::hasTable('dossie_tipos') || !Schema::hasColumn('dossie_tipos', 'modelo_arquivo_id')) {
            return;
        }

        Schema::table('dossie_tipos', function (Blueprint $table) {
            $table->dropIndex('dossie_tipos_modelo_arquivo_id_idx');
            $table->dropColumn('modelo_arquivo_id');
        });
    }
}

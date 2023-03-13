<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSupervisorEtiquetaBloqueioToClienteConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cliente_configs', function (Blueprint $table) {
            $table->boolean('supervisor_etiqueta_bloqueio')->default(true);
        });

        Schema::create('carteira_assinaturas_anexos', function (Blueprint $table) {
            $table->unsignedBigInteger('carteira_assinatura_id');
            $table->foreign('carteira_assinatura_id')->references('id')->on('carteira_assinaturas')->onDelete('CASCADE');
            $table->unsignedInteger('arquivo_id');
            $table->foreign('arquivo_id')->references('id')->on('arquivos')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cliente_configs', function (Blueprint $table) {
            $table->dropColumn('supervisor_etiqueta_bloqueio');
        });

        Schema::dropIfExists('carteira_assinaturas_anexos');
    }
}

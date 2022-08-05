<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentoLegaisSsmasAnexosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documento_legais_ssmas_anexos', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->index('documento_legais_ssmas_anexos_id_foreign');
            $table->unsignedBigInteger('arquivo_id')->index('documento_legais_ssmas_anexos_arquivo_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documento_legais_ssmas_anexos');
    }
}

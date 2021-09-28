<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentosCurriculosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documentos_curriculos', function (Blueprint $table) {
            $table->unsignedBigInteger('curriculo_id')->index('foto_admissaos_curriculo_id_foreign');
            $table->unsignedBigInteger('arquivo_id')->index('foto_admissaos_arquivo_id_foreign');
            $table->string('tipo')->default('foto3x4');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documentos_curriculos');
    }
}

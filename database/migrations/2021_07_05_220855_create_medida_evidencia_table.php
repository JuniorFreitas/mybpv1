<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMedidaEvidenciaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('medida_evidencia', function (Blueprint $table) {
            $table->unsignedBigInteger('medida_id')->index('medida_evidencia_medida_id_foreign');
            $table->unsignedBigInteger('arquivo_id')->index('medida_evidencia_arquivo_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('medida_evidencia');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCihEvidenciaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cih_evidencia', function (Blueprint $table) {
            $table->unsignedBigInteger('cih_id')->index('cih_evidencia_cih_id_foreign');
            $table->unsignedBigInteger('arquivo_id')->index('cih_evidencia_arquivo_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cih_evidencia');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTipoAvisoCurriculoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tipo_aviso_curriculo', function (Blueprint $table) {
            $table->unsignedBigInteger('tipo_aviso_id')->index('tipo_aviso_curriculo_tipo_aviso_id_foreign');
            $table->unsignedBigInteger('feedback_id')->nullable()->index('tipo_aviso_curriculo_feedback_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tipo_aviso_curriculo');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMotivoRescisaoCurriculoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('motivo_rescisao_curriculo', function (Blueprint $table) {
            $table->unsignedBigInteger('motivo_id')->index('motivo_rescisao_curriculo_motivo_id_foreign');
            $table->unsignedBigInteger('feedback_id')->nullable()->index('motivo_rescisao_curriculo_feedback_id_foreign');
            $table->text('outro')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('motivo_rescisao_curriculo');
    }
}

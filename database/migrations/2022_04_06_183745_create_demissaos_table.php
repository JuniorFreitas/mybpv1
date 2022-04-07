<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDemissaosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('demissaos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id');
            $table->boolean('cipa');
            $table->date('data_desmobilizacao');
            $table->unsignedInteger('motivo_rescisao_id');
            $table->text('outro_motivo')->nullable();
            $table->unsignedInteger('tipo_aviso_id');
            $table->string('solicitado_por');
            $table->longText('comentario')->nullable();
            $table->unsignedBigInteger('user_id');

            $table->foreign('feedback_id')->references('id')->on('feedback_curriculos')->cascadeOnDelete();
            $table->foreign('motivo_rescisao_id')->references('id')->on('motivo_rescisao')->cascadeOnDelete();
            $table->foreign('tipo_aviso_id')->references('id')->on('tipo_aviso')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('demissaos');
    }
}

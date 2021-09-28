<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCertificadoAlumarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('certificado_alumar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id')->nullable()->index('certificado_alumar_feedback_id_foreign');
            $table->unsignedBigInteger('cliente_id')->index('certificado_alumar_cliente_id_foreign');
            $table->boolean('nacional');
            $table->unsignedBigInteger('empresa_treinamento_trinta_tres_id')->nullable()->index('certificado_alumar_empresa_treinamento_trinta_tres_id_foreign');
            $table->unsignedBigInteger('empresa_treinamento_trinta_cinco_id')->nullable()->index('certificado_alumar_empresa_treinamento_trinta_cinco_id_foreign');
            $table->unsignedBigInteger('instrutor_trinta_tres_id')->nullable()->index('certificado_alumar_instrutor_trinta_tres_id_foreign');
            $table->unsignedBigInteger('instrutor_trinta_cinco_id')->nullable()->index('certificado_alumar_instrutor_trinta_cinco_id_foreign');
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
        Schema::dropIfExists('certificado_alumar');
    }
}

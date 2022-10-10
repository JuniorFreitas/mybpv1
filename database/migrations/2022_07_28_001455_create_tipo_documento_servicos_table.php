<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTipoDocumentoServicosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tipo_documento_servicos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('tipo')->comment('pode ser para contrato, ssma...');
            $table->boolean('ativo')->default(true);
            $table->unsignedBigInteger('empresa_id');

            $table->foreign('empresa_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tipo_documento_servicos');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurriculoExperienciasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('curriculo_experiencias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('curriculo_id')->index('curriculo_experiencias_curriculo_foreign');
            $table->string('empresa');
            $table->string('cargo');
            $table->text('principais_atv');
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->string('referencia_nome')->nullable();
            $table->string('referencia_telefone')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('curriculo_experiencias');
    }
}

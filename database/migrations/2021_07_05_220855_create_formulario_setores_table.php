<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormularioSetoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('formulario_setores', function (Blueprint $table) {
            $table->unsignedBigInteger('formulario_id')->index('formulario_setores_formulario_id_foreign');
            $table->unsignedBigInteger('setores_id')->index('formulario_setores_setores_id_foreign');
            $table->integer('ordem');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('formulario_setores');
    }
}

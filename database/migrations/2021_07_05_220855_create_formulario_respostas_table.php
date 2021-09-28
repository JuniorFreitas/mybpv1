<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormularioRespostasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('formulario_respostas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('formulario_id')->index('formulario_respostas_formulario_id_foreign');
            $table->unsignedBigInteger('user_id')->index('formulario_respostas_user_id_foreign');
            $table->json('respostas');
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
        Schema::dropIfExists('formulario_respostas');
    }
}

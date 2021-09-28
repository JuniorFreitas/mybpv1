<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFuncionarioEscalasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('funcionario_escalas', function (Blueprint $table) {
            $table->unsignedBigInteger('funcionario_id')->index('funcionario_escalas_funcionario_id_foreign');
            $table->unsignedBigInteger('escala_id')->index('funcionario_escalas_escala_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('funcionario_escalas');
    }
}

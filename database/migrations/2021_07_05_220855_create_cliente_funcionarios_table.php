<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClienteFuncionariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cliente_funcionarios', function (Blueprint $table) {
            $table->unsignedBigInteger('cliente_id')->index('cliente_funcionarios_cliente_id_foreign');
            $table->unsignedBigInteger('funcionario_id')->index('cliente_funcionarios_funcionario_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cliente_funcionarios');
    }
}

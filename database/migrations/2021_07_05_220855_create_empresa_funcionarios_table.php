<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmpresaFuncionariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('empresa_funcionarios', function (Blueprint $table) {
            $table->unsignedBigInteger('empresa_id')->index('empresa_funcionarios_empresa_id_foreign');
            $table->unsignedBigInteger('funcionario_id')->index('empresa_funcionarios_funcionario_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('empresa_funcionarios');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFuncionarioPerimetrosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('funcionario_perimetros', function (Blueprint $table) {
            $table->unsignedBigInteger('funcionario_id')->index('funcionario_perimetros_funcionario_id_foreign');
            $table->unsignedBigInteger('perimetro_id')->index('funcionario_perimetros_perimetro_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('funcionario_perimetros');
    }
}

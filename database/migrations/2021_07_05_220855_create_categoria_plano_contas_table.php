<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriaPlanoContasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categoria_plano_contas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('descricao');
            $table->boolean('ativo');
            $table->unsignedBigInteger('empresa_id')->index('categoria_plano_contas_empresa_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categoria_plano_contas');
    }
}

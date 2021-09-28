<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanoContasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plano_contas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('categoria_plano_id')->nullable()->index('plano_contas_categoria_plano_id_foreign');
            $table->string('descricao');
            $table->string('operacao', 1)->comment('c-credito , d-debito, t-todos');
            $table->boolean('ativo');
            $table->unsignedBigInteger('empresa_id')->index('plano_contas_empresa_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plano_contas');
    }
}

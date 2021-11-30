<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCamposRequisicaoVagasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tipo_contratacaos', function (Blueprint $table) {
            $table->string('nome_indicacao')->after('processo')->nullable();

            $table->string('salario')->after('ppra')->nullable();
            $table->decimal('salario_valor', 11, 2)->after('salario')->nullable();

            $table->string('beneficio')->after('salario_valor')->nullable();
            $table->string('beneficio_excecao')->after('beneficio')->nullable();

            $table->string('treinamento')->after('beneficio_excecao')->nullable();
            $table->string('treinamento_excecao')->after('treinamento')->nullable();

            $table->string('local_trabalho')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tipo_contratacaos');
    }
}

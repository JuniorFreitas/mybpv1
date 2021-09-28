<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToEmpresaFuncionariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('empresa_funcionarios', function (Blueprint $table) {
            $table->foreign('empresa_id')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('funcionario_id')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('empresa_funcionarios', function (Blueprint $table) {
            $table->dropForeign('empresa_funcionarios_empresa_id_foreign');
            $table->dropForeign('empresa_funcionarios_funcionario_id_foreign');
        });
    }
}

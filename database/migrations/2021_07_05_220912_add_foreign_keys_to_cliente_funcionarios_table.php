<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToClienteFuncionariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cliente_funcionarios', function (Blueprint $table) {
            $table->foreign('cliente_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('funcionario_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cliente_funcionarios', function (Blueprint $table) {
            $table->dropForeign('cliente_funcionarios_cliente_id_foreign');
            $table->dropForeign('cliente_funcionarios_funcionario_id_foreign');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToFuncionarioEscalasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('funcionario_escalas', function (Blueprint $table) {
            $table->foreign('escala_id')->references('id')->on('empresa_escalas')->onUpdate('RESTRICT')->onDelete('CASCADE');
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
        Schema::table('funcionario_escalas', function (Blueprint $table) {
            $table->dropForeign('funcionario_escalas_escala_id_foreign');
            $table->dropForeign('funcionario_escalas_funcionario_id_foreign');
        });
    }
}

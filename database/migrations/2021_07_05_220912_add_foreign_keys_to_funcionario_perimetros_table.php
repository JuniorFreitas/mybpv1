<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToFuncionarioPerimetrosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('funcionario_perimetros', function (Blueprint $table) {
            $table->foreign('funcionario_id')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('perimetro_id')->references('id')->on('empresa_perimetros')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('funcionario_perimetros', function (Blueprint $table) {
            $table->dropForeign('funcionario_perimetros_funcionario_id_foreign');
            $table->dropForeign('funcionario_perimetros_perimetro_id_foreign');
        });
    }
}

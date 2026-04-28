<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVinculoTodosCargosToVencimentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vencimentos', function (Blueprint $table) {
            $table->boolean('vinculo_todos_cargos')->default(true)->after('segmento_treinamento_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vencimentos', function (Blueprint $table) {
            $table->dropColumn('vinculo_todos_cargos');
        });
    }
}

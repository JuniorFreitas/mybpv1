<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToPlanejamentoDiarioTarefasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('planejamento_diario_tarefas', function (Blueprint $table) {
            $table->foreign('planejamento_id')->references('id')->on('planejamento_diarios')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('planejamento_diario_tarefas', function (Blueprint $table) {
            $table->dropForeign('planejamento_diario_tarefas_planejamento_id_foreign');
        });
    }
}

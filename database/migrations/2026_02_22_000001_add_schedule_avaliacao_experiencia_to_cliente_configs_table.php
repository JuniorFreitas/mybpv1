<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddScheduleAvaliacaoExperienciaToClienteConfigsTable extends Migration
{
    /**
     * Run the migrations.
     * Permite habilitar/desabilitar por empresa o schedule de Avaliação de Experiência (e-mail de vencimentos) sem deploy.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cliente_configs', function (Blueprint $table) {
            $table->boolean('schedule_avaliacao_experiencia')->default(true)->after('supervisor_etiqueta_bloqueio');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cliente_configs', function (Blueprint $table) {
            $table->dropColumn('schedule_avaliacao_experiencia');
        });
    }
}

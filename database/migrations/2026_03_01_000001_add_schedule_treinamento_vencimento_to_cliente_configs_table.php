<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddScheduleTreinamentoVencimentoToClienteConfigsTable extends Migration
{
    /**
     * Run the migrations.
     * Permite habilitar/desabilitar por empresa o schedule de Treinamento Vencimento sem deploy.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cliente_configs', function (Blueprint $table) {
            $table->boolean('schedule_treinamento_vencimento')
                ->default(true)
                ->after('schedule_avaliacao_experiencia');
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
            $table->dropColumn('schedule_treinamento_vencimento');
        });
    }
}

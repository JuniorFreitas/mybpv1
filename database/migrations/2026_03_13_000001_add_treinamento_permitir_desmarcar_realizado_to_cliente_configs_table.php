<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTreinamentoPermitirDesmarcarRealizadoToClienteConfigsTable extends Migration
{
    /**
     * Run the migrations.
     * Quando ativa, usuários com privilegio_gestao_rh podem alterar
     * "Realizou este treinamento?" para Não quando já estava salvo como realizado.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cliente_configs', function (Blueprint $table) {
            $table->boolean('treinamento_permitir_desmarcar_realizado')
                ->default(false)
                ->after('schedule_treinamento_vencimento');
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
            $table->dropColumn('treinamento_permitir_desmarcar_realizado');
        });
    }
}

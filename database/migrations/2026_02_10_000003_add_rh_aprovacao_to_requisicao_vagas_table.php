<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRhAprovacaoToRequisicaoVagasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requisicao_vagas', function (Blueprint $table) {
            // Adicionar colunas de aprovação RH (3º nível)
            $table->unsignedBigInteger('rh_aprovacao_id')->nullable()->after('status_aprovacao_extra');
            $table->timestamp('data_aprovacao_rh')->nullable()->after('rh_aprovacao_id');
            $table->text('obs_rh')->nullable()->after('data_aprovacao_rh');
            $table->string('status_aprovacao_rh')->nullable()->after('obs_rh');

            $table->foreign('rh_aprovacao_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('requisicao_vagas', function (Blueprint $table) {
            $table->dropForeign(['rh_aprovacao_id']);
            $table->dropColumn([
                'rh_aprovacao_id',
                'data_aprovacao_rh',
                'obs_rh',
                'status_aprovacao_rh'
            ]);
        });
    }
}

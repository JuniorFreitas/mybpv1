<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRhAprovacaoFieldsToTransferenciaPrevistasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transferencia_previstas', function (Blueprint $table) {
            $table->unsignedBigInteger('rh_aprovacao_id')->nullable()->after('gestor_id');
            $table->string('status_aprovacao_rh')->nullable()->after('rh_aprovacao_id');
            $table->text('obs_rh')->nullable()->after('status_aprovacao_rh');
            $table->dateTime('data_aprovacao_rh')->nullable()->after('obs_rh');
            
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
        Schema::table('transferencia_previstas', function (Blueprint $table) {
            $table->dropForeign(['rh_aprovacao_id']);
            $table->dropColumn('rh_aprovacao_id');
            $table->dropColumn('status_aprovacao_rh');
            $table->dropColumn('obs_rh');
            $table->dropColumn('data_aprovacao_rh');
        });
    }
}


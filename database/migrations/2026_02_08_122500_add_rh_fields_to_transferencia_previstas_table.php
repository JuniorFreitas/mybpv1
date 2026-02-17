<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRhFieldsToTransferenciaPrevistasTable extends Migration
{
    public function up()
    {
        Schema::table('transferencia_previstas', function (Blueprint $table) {
            if (!Schema::hasColumn('transferencia_previstas', 'user_rh_id')) {
                $table->unsignedBigInteger('user_rh_id')->nullable()->after('aprovacao_extra_id');
            }
            if (!Schema::hasColumn('transferencia_previstas', 'resposta_rh')) {
                $table->string('resposta_rh')->nullable()->after('user_rh_id');
            }
            if (!Schema::hasColumn('transferencia_previstas', 'obs_rh')) {
                $table->text('obs_rh')->nullable()->after('resposta_rh');
            }
            if (!Schema::hasColumn('transferencia_previstas', 'data_aprovacao_rh')) {
                $table->dateTime('data_aprovacao_rh')->nullable()->after('obs_rh');
            }
        });
    }

    public function down()
    {
        Schema::table('transferencia_previstas', function (Blueprint $table) {
            if (Schema::hasColumn('transferencia_previstas', 'data_aprovacao_rh')) {
                $table->dropColumn('data_aprovacao_rh');
            }
            if (Schema::hasColumn('transferencia_previstas', 'obs_rh')) {
                $table->dropColumn('obs_rh');
            }
            if (Schema::hasColumn('transferencia_previstas', 'resposta_rh')) {
                $table->dropColumn('resposta_rh');
            }
            if (Schema::hasColumn('transferencia_previstas', 'user_rh_id')) {
                $table->dropColumn('user_rh_id');
            }
        });
    }
}

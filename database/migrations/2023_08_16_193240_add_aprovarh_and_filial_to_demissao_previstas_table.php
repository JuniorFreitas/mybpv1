<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAprovarhAndFilialToDemissaoPrevistasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('demissao_previstas', function (Blueprint $table) {
            $table->boolean('filial')->default(false)->nullable();
            $table->unsignedBigInteger('centro_custo_filial_id')->nullable();
            $table->unsignedBigInteger('rh_aprovacao_id')->nullable();
            $table->text('obs_rh')->nullable();
            $table->string('status_aprovacao_rh')->nullable();
            $table->dateTime('data_aprovacao_rh')->nullable();
            $table->boolean('aprovado_via_script')->default(false);
            $table->unsignedBigInteger('quem_deletou_id')->nullable();
            $table->softDeletes();

            $table->foreign('centro_custo_filial_id')->references('id')->on('centro_custo_filials');
            $table->foreign('rh_aprovacao_id')->references('id')->on('users');
            $table->foreign('quem_deletou_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('demissao_previstas', function (Blueprint $table) {
            $table->dropForeign('quem_deletou_id');
            $table->dropForeign('rh_aprovacao_id');
            $table->dropForeign('centro_custo_filial_id');
            $table->dropColumn('quem_deletou_id');
            $table->dropColumn('aprovado_via_script');
            $table->dropColumn('data_aprovacao_rh');
            $table->dropColumn('status_aprovacao_rh');
            $table->dropColumn('obs_rh');
            $table->dropColumn('rh_aprovacao_id');
            $table->dropColumn('centro_custo_filial_id');
        });
    }
}

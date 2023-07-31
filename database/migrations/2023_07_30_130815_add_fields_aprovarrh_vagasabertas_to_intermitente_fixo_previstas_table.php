<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsAprovarrhVagasabertasToIntermitenteFixoPrevistasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('intermitente_fixo_previstas', function (Blueprint $table) {
            $table->unsignedBigInteger('anterior_vaga_aberta_id')->nullable();
            $table->unsignedBigInteger('nova_vaga_aberta_id')->nullable();
            $table->unsignedBigInteger('rh_aprovacao_id')->nullable();
            $table->text('obs_rh')->nullable();
            $table->string('status_aprovacao_rh')->nullable();
            $table->dateTime('data_aprovacao_rh')->nullable();
            $table->boolean('aprovado_via_script')->default(false);
            $table->unsignedBigInteger('quem_deletou_id')->nullable();
            $table->softDeletes();

            $table->foreign('anterior_vaga_aberta_id')->references('id')->on('vagas_abertas');
            $table->foreign('nova_vaga_aberta_id')->references('id')->on('vagas_abertas');
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
        Schema::table('intermitente_fixo_previstas', function (Blueprint $table) {
            $table->dropForeign('quem_deletou_id');
            $table->dropForeign('rh_aprovacao_id');
            $table->dropForeign('nova_vaga_aberta_id');
            $table->dropForeign('anterior_vaga_aberta_id');
            $table->dropColumn('quem_deletou_id');
            $table->dropColumn('aprovado_via_script');
            $table->dropColumn('data_aprovacao_rh');
            $table->dropColumn('status_aprovacao_rh');
            $table->dropColumn('obs_rh');
            $table->dropColumn('rh_aprovacao_id');
            $table->dropColumn('nova_vaga_aberta_id');
            $table->dropColumn('anterior_vaga_aberta_id');
        });
    }
}

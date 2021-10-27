<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNovosCamposRequisicaoVagasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requisicao_vagas', function (Blueprint $table) {
            $table->unsignedBigInteger('user_aprovacao_id')->nullable();
            $table->dateTime('data_aprovacao')->nullable();
            $table->text('obs_aprovacao')->nullable();
            $table->string('status_aprovacao')->nullable();

            $table->foreign('user_aprovacao_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

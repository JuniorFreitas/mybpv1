<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCamposFeriasPrevistaDadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ferias_prevista_dados', function (Blueprint $table) {
            $table->unsignedBigInteger('user_aprovacao_id')->nullable();
            $table->foreign('user_aprovacao_id')->references('id')->on('users');
            $table->dateTime('data_aprovacao')->nullable();
            $table->text('obs_aprovacao')->nullable();
            $table->string('status_aprovacao')->nullable();

            $table->boolean('tem_faltas');
            $table->integer('qnt_faltas')->nullable();
            $table->unsignedBigInteger('user_rh_id')->nullable();
            $table->string('resposta_rh')->nullable();
            $table->text('obs_rh')->nullable();
            $table->dateTime('data_aprovacao_rh')->nullable();
            $table->foreign('user_rh_id')->references('id')->on('users')->cascadeOnDelete();

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

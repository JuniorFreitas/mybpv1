<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTreinamentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('treinamentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id')->nullable()->index('treinamentos_feedback_id_foreign');
            $table->unsignedBigInteger('cadastrou')->nullable()->index('treinamentos_cadastrou_foreign');
            $table->string('tipo')->nullable()->comment('parada, fixo');
            $table->unsignedBigInteger('gerou_id')->nullable()->index('treinamentos_gerou_id_foreign');
            $table->dateTime('data_envio')->nullable();
            $table->boolean('enviado_email')->nullable();
            $table->unsignedBigInteger('enviou_id')->nullable()->index('treinamentos_enviou_id_foreign');
            $table->string('email_envio')->nullable();
            $table->boolean('email_aberto')->nullable();
            $table->dateTime('data_email_aberto')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('treinamentos');
    }
}

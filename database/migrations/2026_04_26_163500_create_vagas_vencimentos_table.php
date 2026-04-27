<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVagasVencimentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vagas_vencimentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('vaga_id');
            $table->unsignedInteger('vencimento_id');
            $table->timestamps();

            $table->unique(['vaga_id', 'vencimento_id'], 'vagas_vencimentos_vaga_id_vencimento_id_unique');
            $table->foreign('vaga_id')->references('id')->on('vagas')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('vencimento_id')->references('id')->on('vencimentos')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vagas_vencimentos');
    }
}

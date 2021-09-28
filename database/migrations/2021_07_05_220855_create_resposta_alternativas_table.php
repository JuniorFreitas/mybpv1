<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRespostaAlternativasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resposta_alternativas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('alternativa_id')->index('resposta_alternativas_alternativa_id_foreign');
            $table->string('label');
            $table->boolean('selecionado')->nullable()->comment('Para os checkbox vir marcado');
            $table->unsignedBigInteger('link_id')->nullable()->index('resposta_alternativas_link_id_foreign');
            $table->integer('ordem');
            $table->bigInteger('value')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('resposta_alternativas');
    }
}

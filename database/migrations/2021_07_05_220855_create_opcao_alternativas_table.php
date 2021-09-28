<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOpcaoAlternativasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('opcao_alternativas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('alternativa_id')->index('opcao_alternativas_alternativa_id_foreign');
            $table->string('label');
            $table->boolean('selecionado')->default(0);
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
        Schema::dropIfExists('opcao_alternativas');
    }
}

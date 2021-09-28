<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVencimentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vencimentos', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('descricao')->nullable();
            $table->integer('prazo_parada')->nullable();
            $table->integer('prazo_fixo')->nullable();
            $table->integer('ordem')->nullable();
            $table->boolean('ativo')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vencimentos');
    }
}

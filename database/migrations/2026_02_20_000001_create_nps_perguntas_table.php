<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNpsPerguntasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nps_perguntas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->text('texto');
            $table->unsignedInteger('ordem')->default(0);
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['empresa_id', 'ativo', 'ordem']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nps_perguntas');
    }
}

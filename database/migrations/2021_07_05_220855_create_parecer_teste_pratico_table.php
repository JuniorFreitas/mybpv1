<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParecerTestePraticoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parecer_teste_pratico', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id')->nullable()->index('parecer_teste_pratico_feedback_id_foreign');
            $table->boolean('fez_teste')->nullable();
            $table->dateTime('data_horario_realizacao')->nullable();
            $table->string('responsavel_pelo_teste')->nullable();
            $table->string('qual_teste')->nullable();
            $table->integer('resultado_teste')->nullable();
            $table->integer('nota_teste')->nullable();
            $table->string('parecer_final_teste')->nullable();
            $table->unsignedBigInteger('entrevistador')->nullable()->index('parecer_teste_pratico_entrevistador_foreign');
            $table->string('quem_entrevistou')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('formulario_id')->nullable()->index('parecer_teste_pratico_formulario_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parecer_teste_pratico');
    }
}

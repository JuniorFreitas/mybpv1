<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeriascalculoavosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ferias_calculo_avos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedInteger('admissao_id');
            $table->unsignedBigInteger('periodo_aquisitivo_id');
            $table->decimal('total_avos', 11, 2)->default(0);
            $table->json('historico')->nullable();
            $table->boolean('atualizado_via_script')->default(false);
            $table->dateTime('ultima_atualizacao');

            $table->foreign('empresa_id')->references('id')->on('users');
            $table->foreign('admissao_id')->references('id')->on('admissoes');
            $table->foreign('periodo_aquisitivo_id')->references('id')->on('periodos_aquisitivos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ferias_calculo_avos');
    }
}

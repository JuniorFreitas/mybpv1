<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmpresaConfiguracoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('empresa_configuracoes', function (Blueprint $table) {
            $table->unsignedBigInteger('empresa_id')->primary();
            $table->string('tipo_frequencia')->default('hora_extra');
            $table->integer('tempo_limite_falta')->default(60);
            $table->integer('tempo_limite_saida')->default(60);
            $table->integer('dia_nova_frequencia')->default(1);
            $table->integer('limite_tolerancia')->default(15);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('empresa_configuracoes');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanejamentoMovimentacaoAnexosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('demissao_previstas_anexos', function (Blueprint $table) {
            $table->unsignedBigInteger('demissao_prevista_id');
            $table->foreign('demissao_prevista_id')->references('id')->on('demissao_previstas')->onDelete('CASCADE');
            $table->unsignedInteger('arquivo_id');
            $table->foreign('arquivo_id')->references('id')->on('arquivos')->onDelete('CASCADE');
        });

        Schema::create('ferias_previstas_anexos', function (Blueprint $table) {
            $table->unsignedBigInteger('ferias_prevista_id');
            $table->foreign('ferias_prevista_id')->references('id')->on('ferias_previstas')->onDelete('CASCADE');
            $table->unsignedInteger('arquivo_id');
            $table->foreign('arquivo_id')->references('id')->on('arquivos')->onDelete('CASCADE');
        });

        Schema::create('admissoes_previstas_anexos', function (Blueprint $table) {
            $table->unsignedBigInteger('admissoes_prevista_id');
            $table->foreign('admissoes_prevista_id')->references('id')->on('admissoes_previstas')->onDelete('CASCADE');
            $table->unsignedInteger('arquivo_id');
            $table->foreign('arquivo_id')->references('id')->on('arquivos')->onDelete('CASCADE');
        });

        Schema::create('valor_extra_previstas_anexos', function (Blueprint $table) {
            $table->unsignedBigInteger('valor_extra_prevista_id');
            $table->foreign('valor_extra_prevista_id')->references('id')->on('valor_extra_previstas')->onDelete('CASCADE');
            $table->unsignedInteger('arquivo_id');
            $table->foreign('arquivo_id')->references('id')->on('arquivos')->onDelete('CASCADE');
        });

        Schema::create('muda_cargo_previstas_anexos', function (Blueprint $table) {
            $table->unsignedBigInteger('muda_cargo_prevista_id');
            $table->foreign('muda_cargo_prevista_id')->references('id')->on('muda_cargo_previstas')->onDelete('CASCADE');
            $table->unsignedInteger('arquivo_id');
            $table->foreign('arquivo_id')->references('id')->on('arquivos')->onDelete('CASCADE');
        });

        Schema::create('intermitente_fixo_previstas_anexos', function (Blueprint $table) {
            $table->unsignedBigInteger('intermitente_fixo_prevista_id');
            $table->foreign('intermitente_fixo_prevista_id','int_fixo_prev_id')->references('id')->on('intermitente_fixo_previstas')->onDelete('CASCADE');
            $table->unsignedInteger('arquivo_id');
            $table->foreign('arquivo_id')->references('id')->on('arquivos')->onDelete('CASCADE');
        });

        Schema::create('transferencia_previstas_anexos', function (Blueprint $table) {
            $table->unsignedBigInteger('transferencia_prevista_id');
            $table->foreign('transferencia_prevista_id')->references('id')->on('transferencia_previstas')->onDelete('CASCADE');
            $table->unsignedInteger('arquivo_id');
            $table->foreign('arquivo_id')->references('id')->on('arquivos')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('demissao_previstas_anexos');
        Schema::dropIfExists('ferias_previstas_anexos');
        Schema::dropIfExists('ferias_previstas_anexos');
        Schema::dropIfExists('admissoes_previstas_anexos');
        Schema::dropIfExists('valor_extra_previstas_anexos');
        Schema::dropIfExists('muda_cargo_previstas_anexos');
        Schema::dropIfExists('intermitente_fixo_previstas_anexos');
        Schema::dropIfExists('transferencia_previstas_anexos');
    }
}

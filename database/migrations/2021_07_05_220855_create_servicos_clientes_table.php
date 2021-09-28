<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicosClientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servicos_clientes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id')->nullable()->index('servicos_clientes_cliente_id_foreign');
            $table->unsignedBigInteger('servico_id')->nullable()->index('servicos_clientes_servico_id_foreign');
            $table->date('data_inicio');
            $table->date('data_encerramento');
            $table->longText('escopo')->nullable();
            $table->decimal('valor', 11);
            $table->string('tipo_faturamento');
            $table->string('status');
            $table->longText('feedback')->nullable();
            $table->boolean('ativo');
            $table->string('tipo_contrato')->default('FIXO');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('servicos_clientes');
    }
}

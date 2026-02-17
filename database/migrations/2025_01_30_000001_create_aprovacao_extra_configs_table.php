<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAprovacaoExtraConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aprovacao_extra_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->enum('tipo_processo', [
                'demissao',
                'ferias',
                'mudanca_cargo',
                'transferencia',
                'intermitente_fixo',
                'valor_extra'
            ])->comment('Tipo de processo que terá aprovação extra');
            $table->string('nome_aprovacao')->comment('Nome da aprovação (ex: SESMT, Supervisor, Gerente)');
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            // Foreign key
            $table->foreign('empresa_id')->references('id')->on('clientes')->onDelete('CASCADE');

            // Index para buscar configurações por empresa e tipo
            $table->index(['empresa_id', 'tipo_processo', 'ativo']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aprovacao_extra_configs');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAniversarianteMensagemTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aniversariante_mensagem_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->longText('conteudo_html');
            $table->unsignedBigInteger('criado_por')->nullable();
            $table->unsignedBigInteger('atualizado_por')->nullable();
            $table->timestamps();

            $table->unique('empresa_id', 'aniversariante_msg_empresa_uidx');
            $table->foreign('empresa_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->foreign('criado_por')->references('id')->on('users')->nullOnDelete();
            $table->foreign('atualizado_por')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aniversariante_mensagem_templates');
    }
}

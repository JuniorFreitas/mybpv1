<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMensagemChatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mensagem_chats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('de_id')->index('mensagem_chats_de_id_foreign');
            $table->unsignedBigInteger('para_id')->nullable()->index('mensagem_chats_para_id_foreign');
            $table->unsignedBigInteger('grupo_id')->nullable()->index('mensagem_chats_grupo_id_foreign');
            $table->string('tipo')->default('txt');
            $table->text('mensagem')->nullable();
            $table->unsignedBigInteger('arquivo_id')->nullable()->index('mensagem_chats_arquivo_id_foreign');
            $table->boolean('visto')->default(0);
            $table->dateTime('datahora_visto')->nullable();
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
        Schema::dropIfExists('mensagem_chats');
    }
}

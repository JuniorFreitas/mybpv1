<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToMensagemChatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mensagem_chats', function (Blueprint $table) {
            $table->foreign('arquivo_id')->references('id')->on('arquivos')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('de_id')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('grupo_id')->references('id')->on('grupos_chat')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('para_id')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mensagem_chats', function (Blueprint $table) {
            $table->dropForeign('mensagem_chats_arquivo_id_foreign');
            $table->dropForeign('mensagem_chats_de_id_foreign');
            $table->dropForeign('mensagem_chats_grupo_id_foreign');
            $table->dropForeign('mensagem_chats_para_id_foreign');
        });
    }
}

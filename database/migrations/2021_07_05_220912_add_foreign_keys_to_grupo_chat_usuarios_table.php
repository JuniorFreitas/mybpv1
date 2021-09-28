<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToGrupoChatUsuariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('grupo_chat_usuarios', function (Blueprint $table) {
            $table->foreign('grupo_id')->references('id')->on('grupos_chat')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('grupo_chat_usuarios', function (Blueprint $table) {
            $table->dropForeign('grupo_chat_usuarios_grupo_id_foreign');
            $table->dropForeign('grupo_chat_usuarios_user_id_foreign');
        });
    }
}

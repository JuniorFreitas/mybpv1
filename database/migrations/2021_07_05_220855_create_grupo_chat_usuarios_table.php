<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGrupoChatUsuariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grupo_chat_usuarios', function (Blueprint $table) {
            $table->unsignedBigInteger('grupo_id')->index('grupo_chat_usuarios_grupo_id_foreign');
            $table->unsignedBigInteger('user_id')->index('grupo_chat_usuarios_user_id_foreign');
            $table->boolean('admin')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('grupo_chat_usuarios');
    }
}

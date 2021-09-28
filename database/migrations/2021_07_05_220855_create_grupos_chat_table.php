<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGruposChatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grupos_chat', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nome');
            $table->unsignedBigInteger('empresa_id')->index('grupos_chat_empresa_id_foreign');
            $table->unsignedBigInteger('criou_id')->index('grupos_chat_criou_id_foreign');
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
        Schema::dropIfExists('grupos_chat');
    }
}

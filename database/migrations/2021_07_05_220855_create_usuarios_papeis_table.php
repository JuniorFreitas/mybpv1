<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsuariosPapeisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usuarios_papeis', function (Blueprint $table) {
            $table->unsignedBigInteger('usuario_id')->index('usuarios_papeis_usuario_id_foreign');
            $table->unsignedBigInteger('papel_id')->index('usuarios_papeis_papel_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usuarios_papeis');
    }
}

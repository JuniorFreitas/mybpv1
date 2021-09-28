<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToUsuariosPapeisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('usuarios_papeis', function (Blueprint $table) {
            $table->foreign('papel_id')->references('id')->on('papeis')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('usuario_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('usuarios_papeis', function (Blueprint $table) {
            $table->dropForeign('usuarios_papeis_papel_id_foreign');
            $table->dropForeign('usuarios_papeis_usuario_id_foreign');
        });
    }
}

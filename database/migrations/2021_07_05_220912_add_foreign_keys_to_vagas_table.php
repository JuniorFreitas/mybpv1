<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToVagasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vagas', function (Blueprint $table) {
            $table->foreign('categoria_id')->references('id')->on('categoria_vagas')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('empresa_id')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vagas', function (Blueprint $table) {
            $table->dropForeign('vagas_categoria_id_foreign');
            $table->dropForeign('vagas_empresa_id_foreign');
        });
    }
}

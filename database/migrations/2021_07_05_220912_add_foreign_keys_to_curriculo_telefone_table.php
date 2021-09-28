<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToCurriculoTelefoneTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('curriculo_telefone', function (Blueprint $table) {
            $table->foreign('curriculo_id')->references('id')->on('curriculos')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('curriculo_telefone', function (Blueprint $table) {
            $table->dropForeign('curriculo_telefone_curriculo_id_foreign');
        });
    }
}

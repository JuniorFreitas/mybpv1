<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToTelefoneFornecedoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('telefone_fornecedores', function (Blueprint $table) {
            $table->foreign('fornecedor_id')->references('id')->on('fornecedores')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('telefone_fornecedores', function (Blueprint $table) {
            $table->dropForeign('telefone_fornecedores_fornecedor_id_foreign');
        });
    }
}

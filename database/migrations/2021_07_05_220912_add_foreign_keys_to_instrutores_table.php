<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToInstrutoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('instrutores', function (Blueprint $table) {
            $table->foreign('arquivo_id')->references('id')->on('arquivos')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('instrutores', function (Blueprint $table) {
            $table->dropForeign('instrutores_arquivo_id_foreign');
        });
    }
}

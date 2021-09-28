<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToInstrutorAnexosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('instrutor_anexos', function (Blueprint $table) {
            $table->foreign('arquivo_id')->references('id')->on('arquivos')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('instrutor_id')->references('id')->on('instrutores')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('instrutor_anexos', function (Blueprint $table) {
            $table->dropForeign('instrutor_anexos_arquivo_id_foreign');
            $table->dropForeign('instrutor_anexos_instrutor_id_foreign');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AnexoTreinamentoFatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('treinamento_vencimento', function (Blueprint $table) {
            $table->unsignedInteger('arquivo_id')->nullable();
            $table->foreign('arquivo_id')->references('id')->on('arquivos')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('treinamento_vencimento', function (Blueprint $table) {
            $table->dropColumn('arquivo_id');
        });
    }
}

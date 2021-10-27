<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmpresaIdRequisicaoVagasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requisicao_vagas', function (Blueprint $table) {
            $table->unsignedBigInteger('cliente_id')->nullable()->change();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->foreign('empresa_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('requisicao_vagas', function (Blueprint $table) {
            $table->dropForeign('empresa_id');
            $table->dropColumn('empresa_id');
        });
    }
}

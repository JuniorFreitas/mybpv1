<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToVagasAbertasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vagas_abertas', function (Blueprint $table) {
            $table->foreign('municipio_id')->references('id')->on('municipios')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('vaga_id')->references('id')->on('vagas')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vagas_abertas', function (Blueprint $table) {
            $table->dropForeign('vagas_abertas_municipio_id_foreign');
            $table->dropForeign('vagas_abertas_vaga_id_foreign');
        });
    }
}

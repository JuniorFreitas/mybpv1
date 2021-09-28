<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToPapeisHabilidadesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('papeis_habilidades', function (Blueprint $table) {
            $table->foreign('habilidade_id')->references('id')->on('habilidades')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('papel_id')->references('id')->on('papeis')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('papeis_habilidades', function (Blueprint $table) {
            $table->dropForeign('papeis_habilidades_habilidade_id_foreign');
            $table->dropForeign('papeis_habilidades_papel_id_foreign');
        });
    }
}

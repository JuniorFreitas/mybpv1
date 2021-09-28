<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePapeisHabilidadesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('papeis_habilidades', function (Blueprint $table) {
            $table->unsignedBigInteger('papel_id')->index('papeis_habilidades_papel_id_foreign');
            $table->unsignedBigInteger('habilidade_id')->index('papeis_habilidades_habilidade_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('papeis_habilidades');
    }
}

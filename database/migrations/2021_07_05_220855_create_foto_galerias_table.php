<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFotoGaleriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('foto_galerias', function (Blueprint $table) {
            $table->unsignedBigInteger('arquivo_id')->index('foto_galerias_arquivo_id_foreign');
            $table->unsignedBigInteger('galeria_id')->index('foto_galerias_galeria_id_foreign');
            $table->unsignedBigInteger('ordem');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('foto_galerias');
    }
}

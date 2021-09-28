<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVagasAbertasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vagas_abertas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vaga_id')->index('vagas_abertas_vaga_id_foreign');
            $table->text('descricao')->nullable();
            $table->unsignedBigInteger('municipio_id')->nullable()->index('vagas_abertas_municipio_id_foreign');
            $table->boolean('ativo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vagas_abertas');
    }
}

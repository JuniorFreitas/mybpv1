<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSetorAlternativasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('setor_alternativas', function (Blueprint $table) {
            $table->unsignedBigInteger('setor_id')->index('setor_alternativas_setor_id_foreign');
            $table->unsignedBigInteger('alternativa_id')->index('setor_alternativas_alternativa_id_foreign');
            $table->boolean('obrigatorio')->default(0);
            $table->integer('min')->nullable();
            $table->integer('max')->nullable();
            $table->integer('ordem');
            $table->string('class_especial')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('setor_alternativas');
    }
}

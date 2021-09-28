<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDossieTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dossie', function (Blueprint $table) {
            $table->unsignedBigInteger('arquivo_id')->index('dossie_arquivo_id_foreign');
            $table->unsignedBigInteger('curriculo_id')->index('dossie_curriculo_id_foreign');
            $table->unsignedBigInteger('feedback_id')->index('dossie_feedback_id_foreign');
            $table->string('tipo');
            $table->string('label');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dossie');
    }
}

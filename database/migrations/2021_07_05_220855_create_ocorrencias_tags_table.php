<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOcorrenciasTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ocorrencias_tags', function (Blueprint $table) {
            $table->unsignedBigInteger('ocorrencia_id')->index('ocorrencias_tags_ocorrencia_id_foreign');
            $table->unsignedBigInteger('tag_id')->index('ocorrencias_tags_tag_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ocorrencias_tags');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToOcorrenciasTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ocorrencias_tags', function (Blueprint $table) {
            $table->foreign('ocorrencia_id')->references('id')->on('ocorrencias')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('tag_id')->references('id')->on('tags')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ocorrencias_tags', function (Blueprint $table) {
            $table->dropForeign('ocorrencias_tags_ocorrencia_id_foreign');
            $table->dropForeign('ocorrencias_tags_tag_id_foreign');
        });
    }
}

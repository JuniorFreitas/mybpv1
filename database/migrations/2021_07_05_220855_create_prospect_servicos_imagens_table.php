<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProspectServicosImagensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prospect_servicos_imagens', function (Blueprint $table) {
            $table->unsignedBigInteger('servicos_prospect_id')->index('prospect_servicos_imagens_servicos_prospect_id_foreign');
            $table->unsignedBigInteger('arquivo_id')->index('prospect_servicos_imagens_arquivo_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prospect_servicos_imagens');
    }
}

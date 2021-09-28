<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicosProspectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servicos_prospects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id')->nullable()->index('servicos_prospects_cliente_id_foreign');
            $table->unsignedBigInteger('servico_id')->nullable()->index('servicos_prospects_servico_id_foreign');
            $table->date('data_envio_proposta');
            $table->longText('escopo')->nullable();
            $table->string('status');
            $table->longText('feedback')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('servicos_prospects');
    }
}

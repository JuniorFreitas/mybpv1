<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBeneficiosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beneficios', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->unsignedBigInteger('tipobeneficio_id')->index('beneficios_tipobeneficio_id_foreign');
            $table->unsignedBigInteger('cliente_id')->index('beneficios_cliente_id_foreign');
            $table->decimal('valor', 11);
            $table->string('aplicacao');
            $table->string('periodicidade');
            $table->decimal('valor_descontado', 11);
            $table->string('opcao_desconto');
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
        Schema::dropIfExists('beneficios');
    }
}

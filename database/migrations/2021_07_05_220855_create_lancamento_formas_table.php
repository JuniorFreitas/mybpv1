<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLancamentoFormasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lancamento_formas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('lancamento_id')->index('lancamento_formas_lancamento_id_foreign');
            $table->unsignedBigInteger('forma_pagamento_id')->index('lancamento_formas_forma_pagamento_id_foreign');
            $table->decimal('valor', 10);
            $table->string('observacoes', 200)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lancamento_formas');
    }
}

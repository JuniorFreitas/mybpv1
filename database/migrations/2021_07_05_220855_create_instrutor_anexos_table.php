<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstrutorAnexosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('instrutor_anexos', function (Blueprint $table) {
            $table->unsignedBigInteger('instrutor_id')->index('instrutor_anexos_instrutor_id_foreign');
            $table->unsignedBigInteger('arquivo_id')->index('instrutor_anexos_arquivo_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('instrutor_anexos');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePessoasEmpresasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pessoas_empresas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id')->index('pessoas_empresas_cliente_id_foreign');
            $table->string('nome');
            $table->string('cpf')->nullable();
            $table->string('email')->nullable();
            $table->string('telefone')->nullable();
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
        Schema::dropIfExists('pessoas_empresas');
    }
}

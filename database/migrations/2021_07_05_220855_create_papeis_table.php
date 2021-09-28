<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePapeisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('papeis', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150);
            $table->string('descricao', 150);
            $table->string('email', 150);
            $table->unsignedBigInteger('empresa_id')->nullable()->index('papeis_empresa_id_foreign');
            $table->string('ativo', 1)->default('s');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('papeis');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParabensEnviadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parabens_enviados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('curriculo_id')->nullable()->index('parabens_enviados_curriculo_id_foreign');
            $table->unsignedBigInteger('cliente_id')->nullable()->index('parabens_enviados_cliente_id_foreign');
            $table->integer('ano');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parabens_enviados');
    }
}

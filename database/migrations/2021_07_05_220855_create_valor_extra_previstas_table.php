<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateValorExtraPrevistasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('valor_extra_previstas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('cliente_id')->index('valor_extra_previstas_cliente_id_foreign');
            $table->unsignedBigInteger('colaborador_id')->nullable()->index('valor_extra_previstas_colaborador_id_foreign');
            $table->unsignedBigInteger('centro_custo_id')->index('valor_extra_previstas_centro_custo_id_foreign');
            $table->string('tipo');
            $table->decimal('periodo_dias', 11)->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index('valor_extra_previstas_user_id_foreign');
            $table->string('solicitante')->nullable();
            $table->text('obs')->nullable();
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
        Schema::dropIfExists('valor_extra_previstas');
    }
}

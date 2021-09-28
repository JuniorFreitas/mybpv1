<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMudaCargoPrevistasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('muda_cargo_previstas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('cliente_id')->index('muda_cargo_previstas_cliente_id_foreign');
            $table->unsignedBigInteger('colaborador_id')->index('muda_cargo_previstas_colaborador_id_foreign');
            $table->unsignedBigInteger('centro_custo_id')->index('muda_cargo_previstas_centro_custo_id_foreign');
            $table->unsignedBigInteger('cargo_anterior_id')->nullable()->index('muda_cargo_previstas_cargo_anterior_id_foreign');
            $table->decimal('salario_anterior', 11)->nullable();
            $table->unsignedBigInteger('novo_cargo_id')->nullable()->index('muda_cargo_previstas_novo_cargo_id_foreign');
            $table->decimal('novo_salario', 11)->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index('muda_cargo_previstas_user_id_foreign');
            $table->string('autorizado_por')->nullable();
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
        Schema::dropIfExists('muda_cargo_previstas');
    }
}

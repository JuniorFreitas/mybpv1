<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdmissoesPrevistasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admissoes_previstas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('cliente_id')->index('admissoes_previstas_cliente_id_foreign');
            $table->unsignedBigInteger('colaborador_id')->nullable()->index('admissoes_previstas_colaborador_id_foreign');
            $table->unsignedBigInteger('centro_custo_id')->index('admissoes_previstas_centro_custo_id_foreign');
            $table->string('tipo_contrato');
            $table->unsignedBigInteger('cargo_id')->index('admissoes_previstas_cargo_id_foreign');
            $table->date('data_admissao');
            $table->decimal('salario', 11);
            $table->unsignedBigInteger('user_id')->nullable()->index('admissoes_previstas_user_id_foreign');
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
        Schema::dropIfExists('admissoes_previstas');
    }
}

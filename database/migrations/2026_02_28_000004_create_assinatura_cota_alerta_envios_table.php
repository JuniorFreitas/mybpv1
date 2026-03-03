<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssinaturaCotaAlertaEnviosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assinatura_cota_alerta_envios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('competencia', 7); // YYYY-MM
            $table->unsignedTinyInteger('percentual');
            $table->unsignedInteger('usadas')->default(0);
            $table->unsignedInteger('limite')->default(0);
            $table->timestamps();

            $table->unique(['empresa_id', 'competencia', 'percentual'], 'assinatura_cota_alerta_unq');
            $table->index(['empresa_id', 'competencia'], 'assinatura_cota_alerta_emp_comp_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assinatura_cota_alerta_envios');
    }
}


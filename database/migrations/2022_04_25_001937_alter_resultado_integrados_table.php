<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterResultadoIntegradosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('resultado_integrados', function (Blueprint $table) {
            $table->unsignedBigInteger('pcmso_id')->nullable()->after('encaminhado_exame_data');
            $table->unsignedBigInteger('empresa_exame_id')->nullable()->after('pcmso_id');
            $table->foreign('pcmso_id')->references('id')->on('pcmsos')->cascadeOnDelete();
            $table->foreign('empresa_exame_id')->references('id')->on('empresa_exames')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}

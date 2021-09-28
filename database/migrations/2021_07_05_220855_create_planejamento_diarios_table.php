<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanejamentoDiariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('planejamento_diarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index('planejamento_diarios_user_id_foreign');
            $table->date('data');
            $table->longText('tarefas_agendadas')->nullable();
            $table->longText('importante')->nullable();
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
        Schema::dropIfExists('planejamento_diarios');
    }
}

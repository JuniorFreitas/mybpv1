<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMedidaAdministrativasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('medida_administrativas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id')->index('medida_administrativas_feedback_id_foreign');
            $table->unsignedBigInteger('user_id')->index('medida_administrativas_user_id_foreign');
            $table->string('solicitante')->nullable();
            $table->string('tipo');
            $table->string('definicao')->nullable();
            $table->text('motivo')->nullable();
            $table->timestamps();
            $table->string('causa')->nullable();
            $table->date('data_solicitacao');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('medida_administrativas');
    }
}

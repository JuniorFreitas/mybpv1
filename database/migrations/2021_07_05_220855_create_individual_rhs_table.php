<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndividualRhsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('individual_rhs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id')->nullable()->index('individual_rhs_feedback_id_foreign');
            $table->string('parecer')->nullable();
            $table->integer('nota')->nullable();
            $table->string('entrevistado_por')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index('individual_rhs_user_id_foreign');
            $table->longText('comentario')->nullable();
            $table->timestamps();
            $table->longText('avaliacao_psicologica')->nullable();
            $table->unsignedBigInteger('formulario_id')->nullable()->index('individual_rhs_formulario_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('individual_rhs');
    }
}

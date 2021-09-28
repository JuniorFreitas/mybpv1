<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromocaoFeedbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promocao_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id')->index('promocao_feedbacks_feedback_id_foreign');
            $table->string('novo_cargo');
            $table->decimal('novo_salario', 11);
            $table->string('motivo');
            $table->double('percentual', 8, 2);
            $table->string('tipo');
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
        Schema::dropIfExists('promocao_feedbacks');
    }
}

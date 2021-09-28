<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMetasFeedbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('metas_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id')->index('metas_feedbacks_feedback_id_foreign');
            $table->string('nome');
            $table->longText('descricao');
            $table->date('data_inicio');
            $table->date('data_fim');
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
        Schema::dropIfExists('metas_feedbacks');
    }
}

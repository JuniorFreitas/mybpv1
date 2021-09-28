<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAfastamentoFeedbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('afastamento_feedbacks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('feedback_id')->index('afastamento_feedbacks_feedback_id_foreign');
            $table->unsignedBigInteger('quem_cadastrou')->index('afastamento_feedbacks_quem_cadastrou_foreign');
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
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
        Schema::dropIfExists('afastamento_feedbacks');
    }
}

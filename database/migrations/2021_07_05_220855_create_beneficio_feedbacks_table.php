<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBeneficioFeedbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beneficio_feedbacks', function (Blueprint $table) {
            $table->unsignedBigInteger('beneficio_id')->index('beneficio_feedbacks_beneficio_id_foreign');
            $table->unsignedBigInteger('feedback_id')->index('beneficio_feedbacks_feedback_id_foreign');
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
        Schema::dropIfExists('beneficio_feedbacks');
    }
}

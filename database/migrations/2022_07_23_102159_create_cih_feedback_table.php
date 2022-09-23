<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCihFeedbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cih_feedback', function (Blueprint $table) {
            $table->unsignedInteger('cih_id');
            $table->unsignedBigInteger('feedback_id');

            $table->foreign('cih_id')->references('id')->on('cihs')->cascadeOnDelete();
            $table->foreign('feedback_id')->references('id')->on('feedback_curriculos')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('feedback_cih');
    }
}

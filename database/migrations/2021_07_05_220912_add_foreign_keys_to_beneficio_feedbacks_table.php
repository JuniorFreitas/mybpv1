<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToBeneficioFeedbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('beneficio_feedbacks', function (Blueprint $table) {
            $table->foreign('beneficio_id')->references('id')->on('beneficios')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('feedback_id')->references('id')->on('feedback_curriculos')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('beneficio_feedbacks', function (Blueprint $table) {
            $table->dropForeign('beneficio_feedbacks_beneficio_id_foreign');
            $table->dropForeign('beneficio_feedbacks_feedback_id_foreign');
        });
    }
}

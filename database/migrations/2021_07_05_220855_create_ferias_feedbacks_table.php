<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeriasFeedbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ferias_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id')->index('ferias_feedbacks_feedback_id_foreign');
            $table->unsignedBigInteger('quem_cadastrou')->index('ferias_feedbacks_quem_cadastrou_foreign');
            $table->integer('ano');
            $table->boolean('comprada')->nullable();
            $table->integer('dias_comprados')->nullable();
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->decimal('valor', 11);
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
        Schema::dropIfExists('ferias_feedbacks');
    }
}

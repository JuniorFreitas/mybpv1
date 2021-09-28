<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntrevistaRhsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entrevista_rhs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id')->nullable()->index('entrevista_rhs_feedback_id_foreign');
            $table->string('parecer')->nullable();
            $table->string('indicado_para')->nullable();
            $table->integer('nota')->nullable();
            $table->string('entrevistado_por')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index('entrevista_rhs_user_id_foreign');
            $table->longText('comentario')->nullable();
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
        Schema::dropIfExists('entrevista_rhs');
    }
}

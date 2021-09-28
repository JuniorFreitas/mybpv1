<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogWeeklyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_weekly', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('quadro_id')->index('log_weekly_quadro_id_foreign');
            $table->unsignedBigInteger('tarefa_id')->nullable()->index('log_weekly_tarefa_id_foreign');
            $table->unsignedBigInteger('user_id')->index('log_weekly_user_id_foreign');
            $table->text('descricao');
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
        Schema::dropIfExists('log_weekly');
    }
}

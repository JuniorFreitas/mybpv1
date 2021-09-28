<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToLogWeeklyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('log_weekly', function (Blueprint $table) {
            $table->foreign('quadro_id')->references('id')->on('quadros')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('tarefa_id')->references('id')->on('tarefas')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('log_weekly', function (Blueprint $table) {
            $table->dropForeign('log_weekly_quadro_id_foreign');
            $table->dropForeign('log_weekly_tarefa_id_foreign');
            $table->dropForeign('log_weekly_user_id_foreign');
        });
    }
}

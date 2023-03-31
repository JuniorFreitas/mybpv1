<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAprovacaoRhToCihsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cihs', function (Blueprint $table) {
            $table->unsignedBigInteger('user_rh_id')->after('status')->nullable();
            $table->string('resposta_rh')->after('user_rh_id')->nullable();
            $table->text('obs_rh')->after('resposta_rh')->nullable();
            $table->dateTime('data_aprovacao_rh')->after('obs_rh')->nullable();

            $table->foreign('user_rh_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cihs', function (Blueprint $table) {
            $table->dropForeign('user_rh_id');
            $table->dropColumn('data_aprovacao_rh');
            $table->dropColumn('obs_rh');
            $table->dropColumn('resposta_rh');
            $table->dropColumn('user_rh_id');
        });
    }
}

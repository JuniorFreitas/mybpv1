<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCamposValorExtraPrevistasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('valor_extra_previstas', function (Blueprint $table) {
            $table->unsignedBigInteger('cliente_id')->nullable()->change();
            $table->unsignedBigInteger('user_aprovacao_id')->nullable();
            $table->foreign('user_aprovacao_id')->references('id')->on('users');

            $table->dateTime('data_aprovacao')->nullable();
            $table->text('obs_aprovacao')->nullable();
            $table->string('status_aprovacao')->nullable();

            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->foreign('empresa_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('valor_extra_previstas', function (Blueprint $table) {
            $table->dropForeign('user_aprovacao_id');
            $table->dropColumn('user_aprovacao_id');
            $table->dropColumn('data_aprovacao');
            $table->dropColumn('obs_aprovacao');
            $table->dropColumn('status_aprovacao');
            $table->dropForeign('empresa_id');
            $table->dropColumn('empresa_id');
        });
    }
}

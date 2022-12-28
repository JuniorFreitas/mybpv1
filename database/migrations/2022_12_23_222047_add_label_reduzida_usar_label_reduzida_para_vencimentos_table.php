<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLabelReduzidaUsarLabelReduzidaParaVencimentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vencimentos', function (Blueprint $table) {
            $table->string('label_reduzida')->nullable();
            $table->boolean('exibir_na_carteira')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vencimentos', function (Blueprint $table) {
            $table->boolean('exibir_na_carteira')->nullable();
            $table->dropColumn('label_reduzida');
        });
    }
}

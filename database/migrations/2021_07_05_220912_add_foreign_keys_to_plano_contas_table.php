<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToPlanoContasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plano_contas', function (Blueprint $table) {
            $table->foreign('categoria_plano_id')->references('id')->on('categoria_plano_contas')->onUpdate('RESTRICT')->onDelete('SET NULL');
            $table->foreign('empresa_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plano_contas', function (Blueprint $table) {
            $table->dropForeign('plano_contas_categoria_plano_id_foreign');
            $table->dropForeign('plano_contas_empresa_id_foreign');
        });
    }
}

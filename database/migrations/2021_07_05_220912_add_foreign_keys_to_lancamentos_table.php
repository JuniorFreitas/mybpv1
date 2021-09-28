<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToLancamentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lancamentos', function (Blueprint $table) {
            $table->foreign('empresa_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('plano_id')->references('id')->on('plano_contas')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('quem_alterou')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('quem_cadastrou')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lancamentos', function (Blueprint $table) {
            $table->dropForeign('lancamentos_empresa_id_foreign');
            $table->dropForeign('lancamentos_plano_id_foreign');
            $table->dropForeign('lancamentos_quem_alterou_foreign');
            $table->dropForeign('lancamentos_quem_cadastrou_foreign');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToAdmissoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admissoes', function (Blueprint $table) {
            $table->foreign('editado_usuario_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('feedback_id')->references('id')->on('feedback_curriculos')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('formulario_id')->references('id')->on('formularios')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('user_avaliacao')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('usuario_desmob')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('usuario_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admissoes', function (Blueprint $table) {
            $table->dropForeign('admissoes_editado_usuario_id_foreign');
            $table->dropForeign('admissoes_feedback_id_foreign');
            $table->dropForeign('admissoes_formulario_id_foreign');
            $table->dropForeign('admissoes_user_avaliacao_foreign');
            $table->dropForeign('admissoes_usuario_desmob_foreign');
            $table->dropForeign('admissoes_usuario_id_foreign');
        });
    }
}

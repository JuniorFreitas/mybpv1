<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPcmsoPcmsoidToExameFuncionariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('exame_funcionarios', function (Blueprint $table) {
            $table->boolean('pcmso')->nullable();
            $table->unsignedBigInteger('pcmso_id')->nullable();
            $table->unsignedBigInteger('exame_tipo_id')->nullable();
            $table->date('encaminhamento_data')->nullable();
            $table->foreign('pcmso_id')->references('id')->on('pcmsos')->cascadeOnDelete();
            $table->foreign('exame_tipo_id')->references('id')->on('exame_tipos')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('exame_funcionarios', function (Blueprint $table) {
//            $table->dropForeign('exame_tipo_id');
//            $table->dropForeign('psmso_id');
            $table->dropColumn('encaminhamento_data');
//            $table->dropColumn('exame_tipo_id');
//            $table->dropColumn('pcmso_id');
//            $table->dropColumn('pcmso');
        });
    }
}

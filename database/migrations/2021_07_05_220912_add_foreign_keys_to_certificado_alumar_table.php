<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToCertificadoAlumarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('certificado_alumar', function (Blueprint $table) {
            $table->foreign('cliente_id')->references('id')->on('clientes')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('empresa_treinamento_trinta_cinco_id')->references('id')->on('empresa_treinamentos')->onUpdate('RESTRICT')->onDelete('SET NULL');
            $table->foreign('empresa_treinamento_trinta_tres_id')->references('id')->on('empresa_treinamentos')->onUpdate('RESTRICT')->onDelete('SET NULL');
            $table->foreign('feedback_id')->references('id')->on('feedback_curriculos')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('instrutor_trinta_cinco_id')->references('id')->on('instrutores')->onUpdate('RESTRICT')->onDelete('SET NULL');
            $table->foreign('instrutor_trinta_tres_id')->references('id')->on('instrutores')->onUpdate('RESTRICT')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('certificado_alumar', function (Blueprint $table) {
            $table->dropForeign('certificado_alumar_cliente_id_foreign');
            $table->dropForeign('certificado_alumar_empresa_treinamento_trinta_cinco_id_foreign');
            $table->dropForeign('certificado_alumar_empresa_treinamento_trinta_tres_id_foreign');
            $table->dropForeign('certificado_alumar_feedback_id_foreign');
            $table->dropForeign('certificado_alumar_instrutor_trinta_cinco_id_foreign');
            $table->dropForeign('certificado_alumar_instrutor_trinta_tres_id_foreign');
        });
    }
}

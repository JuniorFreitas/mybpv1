<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentosCurriculosAdmEmpresaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documentos_curriculos_adm_empresa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('categoria_id')->nullable();
            $table->string('label');
            $table->string('metodo')->nullable();
            $table->text('descricao')->nullable();
            $table->string('tipo');
            $table->string('url_arquivo')->nullable();
            $table->json('configuracoes')->nullable(); // json com as configurações do campo (ex: tamanho máximo, extensões permitidas, etc
            $table->integer('ordem')->default(0);
            $table->boolean('ativo')->default(true);

            $table->foreign('empresa_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('categoria_id')->references('id')->on('documentos_curriculos_cat_adm_empresa');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documentos_curriculos_adm_empresa');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDossieTiposTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dossie_tipos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable()->index();
            $table->string('tipo', 100);
            $table->string('chave', 100);
            $table->string('label', 255);
            $table->string('tipo_modelo', 100)->nullable();
            $table->string('tipo_documento', 100)->nullable();
            $table->boolean('tem_modelo')->default(false);
            $table->boolean('permite_assinatura')->default(false);
            $table->unsignedInteger('ordem')->default(0);
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index(['empresa_id', 'tipo'], 'dossie_tipos_empresa_tipo_idx');
            $table->index(['empresa_id', 'chave'], 'dossie_tipos_empresa_chave_idx');
            $table->index(['empresa_id', 'ativo', 'ordem'], 'dossie_tipos_empresa_ativo_ordem_idx');
            $table->index(['ativo', 'ordem'], 'dossie_tipos_ativo_ordem_idx');

            $table->foreign('empresa_id', 'dossie_tipos_empresa_id_foreign')
                ->references('id')
                ->on('clientes')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dossie_tipos');
    }
}

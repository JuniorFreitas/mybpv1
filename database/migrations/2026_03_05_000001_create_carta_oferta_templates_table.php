<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartaOfertaTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carta_oferta_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('titulo', 150);
            $table->longText('conteudo_html');
            $table->string('status', 20)->default('publicado');
            $table->unsignedInteger('versao')->default(1);
            $table->unsignedBigInteger('criado_por')->nullable();
            $table->unsignedBigInteger('atualizado_por')->nullable();
            $table->timestamps();

            $table->index(['empresa_id', 'status'], 'carta_oferta_template_emp_status_idx');
            $table->foreign('empresa_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->foreign('criado_por')->references('id')->on('users')->nullOnDelete();
            $table->foreign('atualizado_por')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('carta_oferta_templates');
    }
}

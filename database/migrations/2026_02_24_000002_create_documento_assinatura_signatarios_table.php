<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentoAssinaturaSignatariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('documento_assinatura_signatarios')) {
            return;
        }
        Schema::create('documento_assinatura_signatarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('documento_para_assinatura_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('email');
            $table->string('nome');
            $table->string('cpf', 14)->nullable();
            $table->unsignedTinyInteger('ordem')->default(1);
            $table->string('token', 64)->unique();
            $table->string('status', 20)->default('pendente');
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->dateTime('data_assinatura_utc')->nullable();
            $table->json('geolocalizacao')->nullable();
            $table->string('hash_evidencia', 64)->nullable();
            $table->text('recusa_motivo')->nullable();
            $table->timestamps();

            $table->foreign('documento_para_assinatura_id', 'doc_signatarios_doc_id_fk')->references('id')->on('documento_para_assinatura')->onDelete('cascade');
            $table->foreign('user_id', 'doc_signatarios_user_id_fk')->references('id')->on('users')->onDelete('set null');
            $table->index('token');
            $table->index(['documento_para_assinatura_id', 'ordem'], 'doc_assinatura_signatarios_doc_ordem_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documento_assinatura_signatarios');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentoAssinaturaEventosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('documento_assinatura_eventos')) {
            return;
        }
        Schema::create('documento_assinatura_eventos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('documento_para_assinatura_id');
            $table->string('evento', 50);
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->foreign('documento_para_assinatura_id', 'doc_eventos_doc_id_fk')->references('id')->on('documento_para_assinatura')->onDelete('cascade');
            $table->index(['documento_para_assinatura_id', 'created_at'], 'doc_assinatura_eventos_doc_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documento_assinatura_eventos');
    }
}

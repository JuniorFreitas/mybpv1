<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificacaoWhatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notificacao_whats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id')->nullable()->index('notificacao_whats_feedback_id_foreign');
            $table->unsignedBigInteger('vaga_id')->index('notificacao_whats_vaga_id_foreign');
            $table->unsignedBigInteger('etapa_id')->index('notificacao_whats_etapa_id_foreign');
            $table->unsignedBigInteger('messageid');
            $table->unsignedBigInteger('user_id')->index('notificacao_whats_user_id_foreign');
            $table->text('mensagem')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notificacao_whats');
    }
}

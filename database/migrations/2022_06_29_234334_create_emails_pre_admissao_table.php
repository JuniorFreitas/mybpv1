<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailsPreAdmissaoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('emails_pre_admissao', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('curriculo_id');
            $table->unsignedBigInteger('quem_enviou_id');
            $table->string('observacao', 255)->nullable();
            $table->boolean('email_atual')->default(true);
            $table->boolean('email_padrao')->default(false);
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('curriculo_id')->references('id')->on('curriculos')->cascadeOnDelete();
            $table->foreign('quem_enviou_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('emails_pre_admissao');
    }
}

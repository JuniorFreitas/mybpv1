<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCihsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cihs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tag_id')->nullable()->index('cihs_tag_id_foreign');
            $table->string('outra_tag')->nullable();
            $table->unsignedBigInteger('feedback_id')->index('cihs_feedback_id_foreign')->comment('somente quem foi admitido');
            $table->unsignedBigInteger('cliente_id')->index('cihs_cliente_id_foreign')->comment('Cliente empresa');
            $table->unsignedBigInteger('user_lancamento_id')->index('cihs_user_lancamento_id_foreign')->comment('Responsavel pelo lançamenro usuario em sessão');
            $table->unsignedBigInteger('area_id')->nullable()->index('cihs_area_id_foreign');
            $table->string('outra_area')->nullable();
            $table->text('obs_lancamento')->nullable()->comment('Responsavel pela aprovação usuario em sessão');
            $table->dateTime('data_lancamento');
            $table->longText('acao');
            $table->unsignedBigInteger('user_aprovacao_id')->nullable()->index('cihs_user_aprovacao_id_foreign')->comment('Responsavel pela aprovação usuario em sessão');
            $table->text('obs_aprovacao')->nullable()->comment('Responsavel pela aprovação usuario em sessão');
            $table->dateTime('data_aprovacao')->nullable();
            $table->string('status')->nullable()->comment('aberto, aprovado');
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
        Schema::dropIfExists('cihs');
    }
}

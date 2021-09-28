<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIntermitentesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('intermitentes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id')->index('intermitentes_feedback_id_foreign')->comment('somente quem foi admitido');
            $table->unsignedBigInteger('cliente_id')->index('intermitentes_cliente_id_foreign')->comment('Cliente empresa');
            $table->unsignedBigInteger('user_lancamento_id')->index('intermitentes_user_lancamento_id_foreign')->comment('Responsavel pelo lançamenro usuario em sessão');
            $table->unsignedBigInteger('area_id')->nullable()->index('intermitentes_area_id_foreign');
            $table->unsignedBigInteger('tipo_id')->nullable()->index('intermitentes_tipo_id_foreign');
            $table->text('obs_lancamento')->nullable()->comment('Responsavel pela aprovação usuario em sessão');
            $table->dateTime('data_lancamento');
            $table->date('encerramento_previsto');
            $table->string('acao', 255)->nullable();
            $table->unsignedBigInteger('user_aprovacao_id')->nullable()->index('intermitentes_user_aprovacao_id_foreign')->comment('Responsavel pela aprovação usuario em sessão');
            $table->text('obs_aprovacao')->nullable()->comment('Responsavel pela aprovação usuario em sessão');
            $table->dateTime('data_aprovacao')->nullable();
            $table->string('status')->nullable()->comment('aberto, aprovado');
            $table->boolean('devolve_epi')->nullable();
            $table->boolean('devolve_cracha')->nullable();
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
        Schema::dropIfExists('intermitentes');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLancamentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lancamentos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('quem_cadastrou')->index('lancamentos_quem_cadastrou_foreign');
            $table->unsignedBigInteger('quem_alterou')->nullable()->index('lancamentos_quem_alterou_foreign');
            $table->unsignedBigInteger('plano_id')->index('lancamentos_plano_id_foreign');
            $table->text('descricao')->nullable();
            $table->decimal('valor', 10);
            $table->decimal('saldo', 10);
            $table->string('operacao', 1);
            $table->dateTime('data_hora');
            $table->date('data_pendente')->nullable()->comment('quando vai receber ou pagar');
            $table->dateTime('data_hora_concluido')->nullable()->comment('quando recebeu ou pagou');
            $table->boolean('concluido')->default(1);
            $table->timestamps();
            $table->unsignedBigInteger('empresa_id')->index('lancamentos_empresa_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lancamentos');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdmissoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admissoes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('feedback_id')->nullable()->index('admissoes_feedback_id_foreign');
            $table->string('contrato')->nullable();
            $table->string('funcao')->nullable();
            $table->string('cargo')->nullable();
            $table->decimal('salario', 11)->nullable();
            $table->string('status')->nullable();
            $table->string('documento')->nullable();
            $table->string('documento_portaria')->nullable();
            $table->string('tipo_admissao')->nullable();
            $table->string('tipo_treinamento')->nullable();
            $table->string('treinamento')->nullable();
            $table->date('data_treinamento')->nullable();
            $table->string('carteira_treinamento')->nullable();
            $table->string('nr_trinta_tres')->nullable();
            $table->date('data_nr_trinta_tres')->nullable();
            $table->string('nr_trinta_cinco')->nullable();
            $table->date('data_nr_trinta_cinco')->nullable();
            $table->string('trinta_dois_sessenta')->nullable();
            $table->date('data_trinta_dois_sessenta')->nullable();
            $table->string('numero_cracha')->nullable();
            $table->date('data_aso')->nullable();
            $table->boolean('foto_escaneada')->nullable();
            $table->string('status_carteira_treinamento')->nullable();
            $table->unsignedBigInteger('usuario_id')->nullable()->index('admissoes_usuario_id_foreign');
            $table->unsignedBigInteger('editado_usuario_id')->nullable()->index('admissoes_editado_usuario_id_foreign');
            $table->date('data_admissao')->nullable();
            $table->date('data_desmobilizacao')->nullable();
            $table->string('avaliacao')->nullable();
            $table->text('obs_avaliacao')->nullable();
            $table->unsignedBigInteger('user_avaliacao')->nullable()->index('admissoes_user_avaliacao_foreign');
            $table->string('responsavel_feedback')->nullable();
            $table->dateTime('data_avaliacao')->nullable();
            $table->unsignedBigInteger('area_etiqueta_id')->nullable();
            $table->boolean('deu_baixa_epi')->nullable();
            $table->boolean('cipa')->nullable();
            $table->longText('alternativas')->nullable();
            $table->dateTime('data_desmob')->nullable();
            $table->unsignedBigInteger('usuario_desmob')->nullable()->index('admissoes_usuario_desmob_foreign');
            $table->boolean('pendencia')->nullable();
            $table->text('pendencias_quais')->nullable();
            $table->text('outros')->nullable();
            $table->string('preenchido_por_rh')->nullable();
            $table->string('preenchido_por_adm')->nullable();
            $table->string('preenchido_por_ssma')->nullable();
            $table->date('data_entrega_area')->nullable();
            $table->boolean('biometria')->nullable();
            $table->date('data_biometria')->nullable();
            $table->unsignedBigInteger('formulario_id')->nullable()->index('admissoes_formulario_id_foreign');
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
        Schema::dropIfExists('admissoes');
    }
}

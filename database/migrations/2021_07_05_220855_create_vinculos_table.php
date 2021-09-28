<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVinculosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vinculos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id')->nullable()->index('vinculos_feedback_id_foreign');
            $table->unsignedBigInteger('vaga_id')->index('vinculos_vaga_id_foreign');
            $table->boolean('parente');
            $table->string('nome')->nullable();
            $table->string('funcao')->nullable();
            $table->string('grau_parentesco')->nullable();
            $table->boolean('foi_empregado')->nullable();
            $table->string('local_empregado')->nullable();
            $table->string('outra_empresa_parceira')->nullable();
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
        Schema::dropIfExists('vinculos');
    }
}

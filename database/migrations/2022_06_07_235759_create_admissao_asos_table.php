<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdmissaoAsosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admissao_asos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedInteger('admissao_id')->nullable();
            $table->unsignedBigInteger('user_alterou_id')->nullable();
            $table->date('data_aso');
            $table->date('data_vencimento');
            $table->boolean('ativo');
            $table->timestamps();
            
            $table->foreign('empresa_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('admissao_id')->references('id')->on('admissoes');
            $table->foreign('user_alterou_id')->references('id')->on('users'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admissao_asos');
    }
}

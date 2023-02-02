<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeletedAtJornadaPeriodo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    const TABELAS = [
        'empresa_escalas',
        'periodo_jornadas',
        'periodo_ponto_eletronicos',
        'escala_jornadas',
        'empresa_perimetros'
    ];

    public function up()
    {
        foreach (self::TABELAS as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                $table->softDeletes();
                $table->unsignedBigInteger('user_deletou_id')->nullable();
                $table->foreign('user_deletou_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}

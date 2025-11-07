<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTokenExpiracaoToAvaliacaoNoventaVencimentos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('avaliacao_noventa_vencimentos', function (Blueprint $table) {
            $table->string('token_avaliacao', 64)->nullable()->unique()->after('prazo_dia_final')
                ->comment('Token único para acesso público à avaliação');
            $table->timestamp('token_expiracao')->nullable()->after('token_avaliacao')
                ->comment('Data de expiração do token');
            $table->boolean('avaliacao_realizada')->default(false)->after('token_expiracao')
                ->comment('Indica se a avaliação já foi realizada via token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('avaliacao_noventa_vencimentos', function (Blueprint $table) {
            $table->dropColumn(['token_avaliacao', 'token_expiracao', 'avaliacao_realizada']);
        });
    }
}

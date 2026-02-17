<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAprovacaoExtraToFeriasPrevistasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ferias_previstas', function (Blueprint $table) {
            // ID do usuário que fará a aprovação extra (ex: Supervisor, Gerente)
            $table->unsignedBigInteger('aprovacao_extra_id')->nullable()->after('data_aprovacao_rh');

            // Status da aprovação extra (aprovado/reprovado)
            $table->string('status_aprovacao_extra')->nullable()->after('aprovacao_extra_id');

            // Observação da aprovação extra
            $table->text('obs_aprovacao_extra')->nullable()->after('status_aprovacao_extra');

            // Data da aprovação extra
            $table->timestamp('data_aprovacao_extra')->nullable()->after('obs_aprovacao_extra');

            // Foreign key para o aprovador extra
            $table->foreign('aprovacao_extra_id')
                ->references('id')
                ->on('users')
                ->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ferias_previstas', function (Blueprint $table) {
            $table->dropForeign(['aprovacao_extra_id']);
            $table->dropColumn([
                'aprovacao_extra_id',
                'status_aprovacao_extra',
                'obs_aprovacao_extra',
                'data_aprovacao_extra'
            ]);
        });
    }
}

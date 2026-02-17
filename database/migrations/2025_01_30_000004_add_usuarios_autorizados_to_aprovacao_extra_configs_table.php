<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUsuariosAutorizadosToAprovacaoExtraConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('aprovacao_extra_configs', function (Blueprint $table) {
            // Campo JSON para armazenar IDs dos usuários autorizados
            $table->json('usuarios_autorizados')->nullable()->after('nome_aprovacao')
                ->comment('Array de user_ids autorizados a aprovar (além de quem tem privilegio_rh)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('aprovacao_extra_configs', function (Blueprint $table) {
            $table->dropColumn('usuarios_autorizados');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAlertasAssinaturaToClienteConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cliente_configs', function (Blueprint $table) {
            $table->json('assinatura_alerta_user_ids')->nullable()->after('limite_assinaturas_mensal');
            $table->json('assinatura_alerta_grupo_ids')->nullable()->after('assinatura_alerta_user_ids');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cliente_configs', function (Blueprint $table) {
            $table->dropColumn(['assinatura_alerta_user_ids', 'assinatura_alerta_grupo_ids']);
        });
    }
}


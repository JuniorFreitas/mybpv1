<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAssinaturaMascaraToClienteConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cliente_configs', function (Blueprint $table) {
            $table->boolean('assinatura_exibir_ip_completo')->nullable()->after('assinatura_alerta_grupo_ids');
            $table->boolean('assinatura_exibir_cpf_completo')->nullable()->after('assinatura_exibir_ip_completo');
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
            $table->dropColumn(['assinatura_exibir_ip_completo', 'assinatura_exibir_cpf_completo']);
        });
    }
}

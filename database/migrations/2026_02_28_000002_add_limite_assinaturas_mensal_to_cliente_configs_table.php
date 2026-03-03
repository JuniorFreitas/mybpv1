<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLimiteAssinaturasMensalToClienteConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cliente_configs', function (Blueprint $table) {
            $table->unsignedInteger('limite_assinaturas_mensal')->nullable()->after('schedule_avaliacao_experiencia');
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
            $table->dropColumn('limite_assinaturas_mensal');
        });
    }
}


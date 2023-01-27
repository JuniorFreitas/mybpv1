<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableDadosAdmissaos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dados_admissaos', function (Blueprint $table) {
            $table->string('ctps_uf')->after('ctps_serie')->nullable();
            $table->string('cert_reservista_num')->nullable();
            $table->string('cert_reservista_categoria')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dados_admissaos', function (Blueprint $table) {
            $table->dropColumn('ctps_uf');
            $table->dropColumn('cert_reservista_num');
            $table->dropColumn('cert_reservista_categoria');
        });
    }
}

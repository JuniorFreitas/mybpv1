<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmpresaExameIdTableEmpresaExameTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('empresa_exames', function (Blueprint $table) {
            $table->unsignedBigInteger('empresa_exame_id')->nullable();
            $table->foreign('empresa_exame_id')->references('id')->on('empresa_exames')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('empresa_exames', function (Blueprint $table) {
            $table->dropColumn('empresa_exame_id');
        });
    }
}

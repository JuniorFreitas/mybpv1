<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmpresaidRenameClienteidParaParabensenviadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parabens_enviados', function (Blueprint $table) {
            $table->renameColumn('cliente_id','empresa_id');
            $table->string('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parabens_enviados', function (Blueprint $table) {
            $table->renameColumn('empresa_id','cliente_id');
            $table->dropColumn('status');
        });
    }
}

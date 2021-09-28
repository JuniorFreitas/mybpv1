<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToExamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('exames', function (Blueprint $table) {
            $table->foreign('empresa_id')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('exame_tipo_id')->references('id')->on('exame_tipos')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('exames', function (Blueprint $table) {
            $table->dropForeign('exames_empresa_id_foreign');
            $table->dropForeign('exames_exame_tipo_id_foreign');
        });
    }
}

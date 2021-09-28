<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToPivotTestemunhalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pivot_testemunhals', function (Blueprint $table) {
            $table->foreign('arquivo_id')->references('id')->on('arquivos')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('testemunhal_id')->references('id')->on('testemunhals')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pivot_testemunhals', function (Blueprint $table) {
            $table->dropForeign('pivot_testemunhals_arquivo_id_foreign');
            $table->dropForeign('pivot_testemunhals_testemunhal_id_foreign');
        });
    }
}

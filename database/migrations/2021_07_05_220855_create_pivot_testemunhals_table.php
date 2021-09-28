<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePivotTestemunhalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pivot_testemunhals', function (Blueprint $table) {
            $table->unsignedBigInteger('testemunhal_id')->index('pivot_testemunhals_testemunhal_id_foreign');
            $table->unsignedBigInteger('arquivo_id')->index('pivot_testemunhals_arquivo_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pivot_testemunhals');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToLancamentoFormasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lancamento_formas', function (Blueprint $table) {
            $table->foreign('forma_pagamento_id')->references('id')->on('formas_pagamento')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('lancamento_id')->references('id')->on('lancamentos')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lancamento_formas', function (Blueprint $table) {
            $table->dropForeign('lancamento_formas_forma_pagamento_id_foreign');
            $table->dropForeign('lancamento_formas_lancamento_id_foreign');
        });
    }
}

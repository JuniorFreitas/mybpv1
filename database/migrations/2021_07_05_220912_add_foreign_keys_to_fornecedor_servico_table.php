<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToFornecedorServicoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fornecedor_servico', function (Blueprint $table) {
            $table->foreign('fornecedor_id')->references('id')->on('fornecedores')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('tipo_servico_fornecedor_id')->references('id')->on('tipo_servico_fornecedor')->onUpdate('RESTRICT')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fornecedor_servico', function (Blueprint $table) {
            $table->dropForeign('fornecedor_servico_fornecedor_id_foreign');
            $table->dropForeign('fornecedor_servico_tipo_servico_fornecedor_id_foreign');
        });
    }
}

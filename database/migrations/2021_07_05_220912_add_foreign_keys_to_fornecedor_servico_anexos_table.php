<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToFornecedorServicoAnexosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fornecedor_servico_anexos', function (Blueprint $table) {
            $table->foreign('arquivo_id')->references('id')->on('arquivos')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('fornecedor_servico_id')->references('id')->on('fornecedor_servico')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fornecedor_servico_anexos', function (Blueprint $table) {
            $table->dropForeign('fornecedor_servico_anexos_arquivo_id_foreign');
            $table->dropForeign('fornecedor_servico_anexos_fornecedor_servico_id_foreign');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNomePessoaToAdmissoesPrevistasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admissoes_previstas', function (Blueprint $table) {
            $table->string('nome_pessoa', 255)->nullable()->after('colaborador_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admissoes_previstas', function (Blueprint $table) {
            $table->dropColumn('nome_pessoa');
        });
    }
}

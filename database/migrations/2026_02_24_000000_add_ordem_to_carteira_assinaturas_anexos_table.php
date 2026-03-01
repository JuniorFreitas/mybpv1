<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrdemToCarteiraAssinaturasAnexosTable extends Migration
{
    /**
     * Run the migrations.
     * O controller CarteiraAssinaturaController usa updateExistingPivot(..., ['ordem' => $index]).
     *
     * @return void
     */
    public function up()
    {
        Schema::table('carteira_assinaturas_anexos', function (Blueprint $table) {
            if (!Schema::hasColumn('carteira_assinaturas_anexos', 'ordem')) {
                $table->unsignedInteger('ordem')->default(0)->after('arquivo_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('carteira_assinaturas_anexos', function (Blueprint $table) {
            if (Schema::hasColumn('carteira_assinaturas_anexos', 'ordem')) {
                $table->dropColumn('ordem');
            }
        });
    }
}
